<?php


namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use App\Exceptions\MessageException;
use App\Http\UseCases\Call\AudioCallService;
use App\Http\UseCases\Messages\MessagesService;
use App\Http\UseCases\User\UserService;
use App\Models\User;
use Exception;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\DB;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class ChatSocket extends BaseSocket
{
    protected SplObjectStorage $clients;
    private UserService $userService;
    private AudioCallService $callService;
    private MessagesService $messagesService;
    protected array $audioClients;

    private static array $errorCallStatuses = [400, 401, 402, 403];

    public function __construct(UserService $userService, AudioCallService $callService, MessagesService $messagesService)
    {
        $this->clients = new SplObjectStorage();
        $this->userService = $userService;
        $this->callService = $callService;
        $this->messagesService = $messagesService;
        $this->audioClients = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s ' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv === 1 ? '' : 's ');

        $data = json_decode($msg, true, 512, JSON_THROW_ON_ERROR);

        switch ($data['type']) {
            case "subscribe":
                $this->userService->setSocketId($data['sender_id'], $from->resourceId);
                break;
            case "message":
                $this->sendMessage($data, $from);
                break;
            case "init_call":
                $data['status'] = (int)$data['status'];
                dump($data);
                $this->initialCall($data);
                break;
            case "call":
                $data['status'] = (int)$data['status'];
                $this->voiceCall($data);
                break;
            case "close_call":
                $data['status'] = (int)$data['status'];
                dump($data);
                $this->closeVoiceCall($data);
                break;
            case "remove_message":
                $this->removeMessage($from, $data);
                break;
            default:
                $this->onClose($from);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function sendMessage($data, $from): void
    {
        $message = null;

        try {
            $message = $this->messagesService->create($data);
        } catch (MessageException $exception) {
            $this->onError($from, $exception);
        }

        $receiverIds = $this->getSocketIdByChatRoom($data['sender_id'], $data['receiver_id']);

        $responseData = [
            "type" => $data['type'],
            "sender_id" => $message->sender_id,
            "message_id" => $message->id,
            "message" => $message->message,
            "username" => $message->username,
            "audio" => $data['audio'],
            "chat_room_id" => $message->chat_room_id,
            "created_at" => $message->created_at
        ];

        $this->broadcast($receiverIds, $responseData);
    }

    public function initialCall($data)
    {
        if ($data['status'] === 100) {
            $this->makeOutboundCall($data);
        } elseif ($data['status'] === 200) {
            $this->acceptCall($data);
        }
    }

    public function makeOutboundCall(array $data): void
    {
        $call = $this->callService->create($data);
        $user = $this->getUser($data['sender_id']);

        $this->audioClients[$call->id] = [
            $data['sender_id'] => $this->userService->getSocketId($data['sender_id']),
            $data['receiver_id'] => $this->userService->getSocketId($data['receiver_id'])
        ];

        $receiver = $this->getReceiver($call->id, $data['sender_id']);
        $sender = $this->getReceiver($call->id, $data['receiver_id']);

        $receiverResponseData = [
            "type" => $data['type'],
            "status" => $call->status,
            "call_id" => $call->id,
            "sender_id" => $data['sender_id'],
            "username" => $user->username
        ];

        $senderResponseData = [
            "type" => $data['type'],
            "status" => 101,
            "call_id" => $call->id
        ];

        $this->sendTo($sender, $senderResponseData);
        $this->sendTo($receiver, $receiverResponseData);

        if ($this->callService->checkExist($data['receiver_id'])) {
            $this->sendTo($sender, ['status' => 500, "username" => $user->username]);
            $this->sendTo($receiver, ['status' => 501, "username" => $user->username]);
            $this->callService->close($call->id, 500);
        }
    }

    public function acceptCall(array $data): void
    {
        $call = $this->callService->accept($data['call_id'], $data['status']);

        if (!$call) {
            throw new RuntimeException('Can`t accept the call');
        }

        $responseData = ["status" => $call->status, "call_id" => $call->id];
        $receiver = $this->getReceiver($data['call_id'], $data['sender_id']);

        $this->sendTo($receiver, $responseData);
    }

    public function voiceCall($data)
    {
        $receiver = $this->getReceiver($data['call_id'], $data['sender_id']);
        $this->sendTo($receiver, $data);
    }

    public function closeVoiceCall($data): void
    {
        if (in_array($data['status'], self::$errorCallStatuses, true)) {
            $receiver = $this->getReceiver($data['call_id'], $data['sender_id']);
            $this->sendTo($receiver, $data);
            $this->callService->close($data['call_id'], $data['status'], $data['duration']);
        }
    }

    public function removeMessage($from, mixed $data)
    {
        try {
            $this->messagesService->removeForAll($data['message_id']);
            [$sender, $receiver] = [$this->userService->getSocketId($data['sender_id']), $this->userService->getSocketId($data['receiver_id'])];
            $this->sendTo($sender, $data);
            $this->sendTo($receiver, $data);
        } catch (\DomainException $exception) {
            $this->sendTo($from, (array)$exception->getMessage());
        }
    }

    private function broadcast($receivers, $data): void
    {
        foreach ($receivers as $receiver) {
            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiver->socket_id) {
                    $client->send(json_encode($data, JSON_THROW_ON_ERROR));
                }
            }
        }
    }

    private function sendTo($receiver, array $data): void
    {
        foreach ($this->clients as $client) {
            if ($client->resourceId == $receiver) {
                $client->send(json_encode($data, JSON_THROW_ON_ERROR));
            }
        }
    }

    private function getReceiver($callId, $userId)
    {
        $receiver = array_filter($this->audioClients[$callId], function ($socketId) use ($userId) {
            return $socketId !== $userId;
        }, ARRAY_FILTER_USE_KEY);

        return reset($receiver);
    }

    private function getSocketIdByChatRoom(int $senderId, int $receiverId)
    {
        return Db::table('users')
            ->select('socket_id')
            ->whereIn('id', [$senderId, $receiverId])
            ->get();
    }

    public function getUser($id)
    {
        return User::find($id);
    }

}

