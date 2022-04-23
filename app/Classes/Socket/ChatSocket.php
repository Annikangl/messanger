<?php


namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\MessageController;
use App\Http\UseCases\User\UserService;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Ratchet\ConnectionInterface;

class ChatSocket extends BaseSocket
{
    protected \SplObjectStorage $clients;
    protected UserService $userService;
    protected MessageController $message;
    protected array $audioClients;
    public CallController $call;

    private static array $errorCallStatuses = [
        400,
        401,
        402,
        403
    ];


    public function __construct(UserService $userService)
    {
        $this->clients = new \SplObjectStorage();
        $this->userService = $userService;
        $this->message = new MessageController();
        $this->call = new CallController();
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
                $this->updateSocketId($data['sender_id'], $from->resourceId);
                break;
            case "message":
                $this->sendMessage($data);
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

    public function getSocketIdByChatRoom(int $senderId, int $receiverId)
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

    public function initialCall($data)
    {
        // Send call notification to receiver user
        if ($data['status'] === 100) {
            $call = $this->makeOutboundCall($data);
            $username = User::find($data['sender_id']);

            $responseData = [
                "type" => $data['type'],
                "status" => $call->status,
                "call_id" => $call->id,
                "sender_id" => $data['sender_id'],
                "username" => $username->username
            ];

            $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
                return $socketId !== $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = reset($receiver);

            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiver) {
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }

//        Receiver user accepted the call
        if ($data['status'] === 200) {
            $this->acceptCall($data);
        }

        // Errors statuses

        if (in_array($data['status'], self::$errorCallStatuses, true)) {
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

    public function makeOutboundCall(array $data)
    {
        $this->audioClients = [
            $data['sender_id'] => $this->getSocketIdByUser($data['sender_id']),
            $data['receiver_id'] => $this->getSocketIdByUser($data['receiver_id'])
        ];

        return $this->call->store($data);
    }

    public function acceptCall(array $data): void
    {
        $call = $this->call->update($data);

//        $user = User::find($data['sender_id']);
//        $user->call_address = $data['call_address'];
//
//        $user->save();

        if ($call) {
            $responseData = [
                "status" => $call->status,
                "call_id" => $call->id
            ];

            $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
                return $socketId !== $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = reset($receiver);

            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiver) {
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }
    }

    public function voiceCall($data)
    {
        $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
            return $socketId !== $data['sender_id'];
        }, ARRAY_FILTER_USE_KEY);

        $receiver = reset($receiver);

        foreach ($this->clients as $client) {
            if ($client->resourceId == $receiver) {
//                dump('Send to ' . $client->resourceId . ' receiver id ' . $receiver);
                $client->send(json_encode($data, JSON_THROW_ON_ERROR));
            }
        }
//        if ($data['status'] == 201) {
//            $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
//                return $socketId !== $data['sender_id'];
//            }, ARRAY_FILTER_USE_KEY);
//
//            $receiver = reset($receiver); // socket id
//
//            dump($data);
//
//            $responseData = [
//                "type" => $data['type'],
//                "sender_id" => $data['sender_id'],
//                "voice_audio" => $data['voice_audio']
//            ];
//
//            foreach ($this->clients as $client) {
//                $client->send(json_encode($responseData));
////                if ($client->resourceId == $receiver) {
//////                    dump('Sending voice  to ' . $responseData['sender_id'] . ' socket '. $receiver);
////                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
////                }
//            }
//        }
    }
}

