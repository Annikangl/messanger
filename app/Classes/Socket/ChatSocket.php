<?php


namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\MessageController;
use App\Models\Call;
use Illuminate\Support\Facades\DB;
use Ratchet\ConnectionInterface;

class ChatSocket extends BaseSocket
{
    protected \SplObjectStorage $clients;
    protected MessageController $message;
    protected array $audioClients;
    public CallController $call;

    protected $caller = null;
    protected $receiver = null;

    public $callerId = null;
    public $receiverSocketId = null;
    public $senderSocketId = null;

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
//            case "call":
//                $this->sendVoiceCall($data);
//                break;
            case "init_call":
                $data['status'] = (int) $data['status'];
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

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function updateSocketId($userId,$socketId): int
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
     * @throws \JsonException
     */
    public function initialCall($data)
    {

        // Send call notification to receiver user
        if ($data['status'] === 100) {
            $call = $this->makeOutboundCall($data);

            $responseData = [
                "type" => $data['type'],
                "status" => $call->status,
                "call_id" => $call->id,
                "sender_id" => $data['sender_id'],
            ];

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


//            $this->audioClients = [
//                $data['sender_id'] => $this->getSocketIdByUser($data['sender_id']),
//                $data['receiver_id'] => $this->getSocketIdByUser($data['receiver_id'])
//            ];

//            $this->receiverSocketId = $this->getSocketIdByUser($data['receiver_id']);
//            $this->callerId = $data['sender_id'];
//            $this->senderSocketId = $this->getSocketIdByUser($data['sender_id']);

//            $this->call = Call::create([
//                "sender_id" => $data['sender_id'],
//                "receiver_id" => $data['receiver_id'],
//                "status" => $data['status'],
//            ]);

//            if ($this->call) {
//                $responseData = [
//                    "type" => $data['type'],
//                    "sender_id" => $data['sender_id'],
//                ];
//
//                foreach ($this->clients as $client) {
//                    if ($client->resourceId === $this->receiverSocketId) {
//                        dump('Initialed call');
//                        $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
//                    }
//                }
//            }
        }

//        Receiver user accepted the call
        if ($data['status'] === 200) {
            dump('status ' . $data['status']);
            $this->acceptCall($data);
//            $receiverId = $this->getSocketIdByUser($this->callerId);
//            $responseData = [
//                "status" => $data['status']
//            ];
//
//            foreach ($this->clients as $client) {
//                if ($client->resourceId === $receiverId) {
//                    dump('Send status ' . $responseData['status'] . ' to socket ' . $receiverId);
//                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
//                }
//            }
        }

        if ($data['status'] === 201) {
//            $receiver = array_filter($this->audioClients, function ($id) use ($data) {
//                return $id !== $data['sender_id'];
//            }, ARRAY_FILTER_USE_KEY);
//
//            $receiver = reset($receiver);

            $this->sendVoiceInCall($data);

//            $responseData = [
//                "type" => 'call',
//                "sender_id" =>  $this->getSocketIdByUser($data['sender_id']),
//                "receiver_id" => $receiver,
//                "voice_audio" => $data['voice_audio']
//            ];

//            $this->sendVoiceCall($responseData);
        }

        if ($data['status'] === 400 || $data['status'] === 450) {
            dump($data);
            $responseData = [
                "status" => $data['status']
            ];

            dump('Status ' . $data['status']);

            foreach ($this->clients as $client) {
                foreach ($this->audioClients as $receiver) {
                    if ($client->resourceId === $receiver) {
                        dump('Close call ' . $receiver . ' status ' . $responseData['status']);
                        $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                    }
                }
            }
        }
    }

    public function makeOutboundCall(array $data): Call|\Illuminate\Http\JsonResponse
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

        dump($call->status);
        if ($call) {
            $responseData = [
                "status" => $call->status
            ];

            $receiver = array_filter($this->audioClients, function ($socketId) use ($data) {
                return $socketId !== $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = reset($receiver);

            dump('Receiver with 200 ' . $receiver);

            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiver) {
                    dump('Send status ' . $responseData['status'] . ' to socket ' . $receiver);
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }
    }

    /**
     * @throws \JsonException
     */
    public function sendVoiceInCall(array $data): void
    {
        $receiver = array_filter($this->audioClients, function ($userId) use ($data) {
            return $userId !== $data['sender_id'];
        }, ARRAY_FILTER_USE_KEY);

        $receiver = reset($receiver);

        $responseData = [
            "type" => 'call',
            "sender_id" =>  $data['sender_id'],
            "receiver_id" => $receiver,
            "voice_audio" => $data['voice_audio']
        ];

        $this->sendVoiceCall($responseData);
    }

    /**
     * @throws \JsonException
     */
    public function sendVoiceCall($data): void
    {
        $receiverId ??= $data['receiver_id'];

        $responseData = [
            'type' => $data['type'],
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'voice_audio' => $data['voice_audio']
        ];

        if ($receiverId) {
            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiverId) {
                    dump('Sending voice from ' . $responseData['receiver_id'] . ' to ' . $responseData['sender_id']);
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }


    }
}

