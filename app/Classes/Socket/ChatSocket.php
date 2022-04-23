<?php


namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Api\MessageController;
use App\Http\UseCases\Call\AudioCallService;
use App\Http\UseCases\User\UserService;
use Exception;
use Illuminate\Support\Facades\DB;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class ChatSocket extends BaseSocket
{
    protected SplObjectStorage $clients;
    protected UserService $userService;
    protected AudioCallService $callService;
    protected MessageController $message;
    protected array $audioClients;

    private static array $errorCallStatuses = [
        400,
        401,
        402,
        403
    ];


    public function __construct(UserService $userService, AudioCallService $callService)
    {
        $this->clients = new SplObjectStorage();
        $this->userService = $userService;
        $this->callService = $callService;
        $this->message = new MessageController();
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
                $this->initialCall($data);
                dump($data);
                break;
            case "call":
                $data['status'] = (int)$data['status'];
                $this->voiceCall($data);
                break;
            case "close_call":
                $data['status'] = (int)$data['status'];
                $this->closeVoiceCall($data);
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

    public function getSocketIdByChatRoom(int $senderId, int $receiverId)
    {
        return Db::table('users')
            ->select('socket_id')
            ->whereIn('id', [$senderId, $receiverId])
            ->get();
    }

    public function sendMessage($data, $from): void
    {
        $receiverIds = $this->getSocketIdByChatRoom($data['sender_id'], $data['receiver_id']);
        $receiver = $this->userService->getSocketId($data['receiver_id']);
        $message = $this->message->store($data);

        $responseData = [
            "type" => $data['type'],
            "sender_id" => $message->sender_id,
            "message_id" => $message->id,
            "message" => $message->message,
            "username" => $message->username,
            "audio" => $message->audio,
            "chat_room_id" => $message->chat_room_id,
            "created_at" => $message->created_at
        ];

        foreach ($this->clients as $client) {
            foreach ($receiverIds as $receiver) {
                if ($client->resourceId === $receiver->socket_id) {
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }
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
        $user = $this->userService->getUser($data['sender_id']);

        $this->audioClients[$call->id] = [
            $data['sender_id'] => $this->userService->getSocketId($data['sender_id']),
            $data['receiver_id'] => $this->userService->getSocketId($data['receiver_id'])
        ];

//        $receiver = $this->userService->getSocketId($call->receiver_id);

        $receiver = array_filter($this->audioClients[$call->id], function ($socketId) use ($data) {
            return $socketId !== $data['sender_id'];
        }, ARRAY_FILTER_USE_KEY);

        $receiver = reset($receiver);

        $responseData = [
            "type" => $data['type'],
            "status" => $call->status,
            "call_id" => $call->id,
            "sender_id" => $data['sender_id'],
            "username" => $user->username
        ];

        $this->sendTo($receiver, $responseData);
    }

    public function acceptCall(array $data): void
    {
        $call = $this->callService->changeStatus($data['call_id'], $data['status']);

        $responseData = [
            "status" => $call->status,
            "call_id" => $call->id
        ];

//        $receiver = $this->userService->getSocketId($call->sender_id);
        $receiver = array_filter($this->audioClients[$call->id], function ($socketId) use ($data) {
            return $socketId !== $data['sender_id'];
        }, ARRAY_FILTER_USE_KEY);

        $receiver = reset($receiver);

        $this->sendTo($receiver, $responseData);
    }

    public function voiceCall($data)
    {
        $receiver = array_filter($this->audioClients[$data['call_id']], function ($socketId) use ($data) {
            return $socketId !== $data['sender_id'];
        }, ARRAY_FILTER_USE_KEY);

        $receiver = reset($receiver);

        $this->sendTo($receiver, $data);
    }

    public function closeVoiceCall($data): void
    {
        if (in_array($data['status'], self::$errorCallStatuses, true)) {
            $call_id = $data['call_id'];

            $receiver = array_filter($this->audioClients[$call_id], function ($socketId) use ($data) {
                return $socketId !== $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = reset($receiver);
            $this->sendTo($receiver, $data);

            unset($this->audioClients[$call_id]);
        }
    }

    private function sendTo($receiver, $data): void
    {
        foreach ($this->clients as $client) {
            if ($client->resourceId == $receiver) {
                $client->send(json_encode($data, JSON_THROW_ON_ERROR));
            }
        }
    }
}

