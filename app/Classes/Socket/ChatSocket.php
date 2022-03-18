<?php


namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\MessageController;
use App\Models\Call;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use JsonException;
use Ratchet\ConnectionInterface;

class ChatSocket extends BaseSocket
{
    protected \SplObjectStorage $clients;
    protected MessageController $message;
    protected array $audioClients;
    public CallController $call;

    private array $errorCallStatuses = [
        400,
        401,
        402,
        403
    ];


    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->message = new MessageController();
        $this->call = new CallController();
        $this->audioClients = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s ' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv === 1 ? '' : 's ');

        $data = json_decode($msg, true, 512, JSON_THROW_ON_ERROR);

        switch ($data['type']) {
            case "subscribe":
                $this->updateSocketId($data['sender_id'], $from->resourceId);
                break;
            case "message":
                $this->sendMessage($data);
                break;
            case "init_call":
                $data['status'] = (int)$data['status'];
                $this->initialCall($data);
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

    public function updateSocketId($userId, $socketId): int
    {
        return DB::table('users')
            ->where('id', $userId)
            ->update(['socket_id' => $socketId]);
    }

    public function getSocketIdByChatRoom(int $senderId, int $receiverId): \Illuminate\Support\Collection
    {
        return Db::table('users')
            ->select('socket_id')
            ->whereIn('id', [$senderId, $receiverId])
            ->get();
    }

    public function getSocketIdByUser(int $userId)
    {
        return DB::table('users')
            ->select('socket_id')
            ->where('id', [$userId])->
            value('socket_id');
    }

    public function sendMessage($data): void
    {
        $receiverIds = $this->getSocketIdByChatRoom($data['sender_id'], $data['receiver_id']);
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

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function initialCall($data)
    {
        // Send call notification to receiver user
        if ($data['status'] === 100) {
            $call = $this->makeOutboundCall($data);
            $user = User::find($data['sender_id'])->select('username')
                ->first();

            $responseData = [
                "type" => $data['type'],
                "status" => $call->status,
                "call_id" => $call->id,
                "sender_id" => $data['sender_id'],
                "username" => $user->username
            ];

            dump('Response data', $responseData);

            $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
                return $socketId !== $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = reset($receiver);

            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiver) {
                    dump('Make new call to ' . $receiver . ' with status ' . $responseData['status']);
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }

//        Receiver user accepted the call
        if ($data['status'] === 200) {
            $this->acceptCall($data);
        }

        if (in_array($data['status'], $this->$errorCallStatuses, true)) {
            $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
                return $socketId !== $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = reset($receiver);

            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiver) {
                    $client->send(json_encode($data, JSON_THROW_ON_ERROR));
                }
            }
        }

    }

    /**
     * @throws Exception
     */
    public function makeOutboundCall(array $data): Call|JsonResponse
    {
        $this->audioClients = [
            $data['sender_id'] => $this->getSocketIdByUser($data['sender_id']),
            $data['receiver_id'] => $this->getSocketIdByUser($data['receiver_id'])
        ];

        $user = User::find($data['sender_id']);
        $user->call_address = $data['call_address'];
        if (!$user->save()) {
            throw new Exception('User not update IP');
        }

        return $this->call->store($data);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function acceptCall(array $data): void
    {
        $call = $this->call->update($data);

        $user = User::find($data['sender_id']);
        $user->call_address = $data['call_address'];

        if (!$user->save()) {
            throw new Exception('User not update IP');
        }

        if ($call) {

            $responseData = [
                "status" => $call->status
            ];

            $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
                return $socketId !== $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = reset($receiver);

            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiver) {
//                    dump('Send status ' . $responseData['status'] . ' to socket ' . $receiver);
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }
    }
}

