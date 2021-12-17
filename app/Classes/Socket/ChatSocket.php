<?php


namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Api\MessageController;
use App\Models\Call;
use Illuminate\Support\Facades\DB;
use Ratchet\ConnectionInterface;

class ChatSocket extends BaseSocket
{
    protected $clients;
    protected $message;
    protected $audioClients;
    public $call = null;

    protected $caller = null;
    protected $receiver = null;

    public $callerId = null;
    public $receiverSocketId = null;
    public $senderSocketId = null;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->message = new MessageController();
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
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv === 1 ? '' : 's');

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

    public function sendMessage($data) {
        $receiverIds = $this->getSocketIdByChatRoom($data['sender_id'], $data['receiver_id']);
        $message = $this->message->store($data);

        $responseData = [
            "type" => $data['type'],
            "sender_id" => $message->sender_id,
            "message" => $message->message,
            "username" => $message->username,
            "audio" => $message->audio,
            "chat_room_id" => $message->chat_room_id,
            "created_at" => date('H:i', strtotime($message->created_at))
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

            $this->audioClients = [
                $data['sender_id'] => $this->getSocketIdByUser($data['sender_id']),
                $data['receiver_id'] => $this->getSocketIdByUser($data['receiver_id'])
            ];

            $this->receiverSocketId = $this->getSocketIdByUser($data['receiver_id']);
            $this->callerId = $data['sender_id'];
            $this->senderSocketId = $this->getSocketIdByUser($data['sender_id']);

            $this->call = Call::create([
                "sender_id" => $data['sender_id'],
                "receiver_id" => $data['receiver_id'],
                "status" => $data['status'],
            ]);

            if ($this->call) {
                $responseData = [
                    "type" => $data['type'],
                    "sender_id" => $data['sender_id'],
                ];

                foreach ($this->clients as $client) {
                    if ($client->resourceId === $this->receiverSocketId) {
                        dump('Initialed call');
                        $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                    }
                }
            }
        }

//        Receiver user accepted the call
        if ($data['status'] === 200) {

            $receiverId = $this->getSocketIdByUser($this->callerId);
            $responseData = [
                "status" => $data['status']
            ];

            foreach ($this->clients as $client) {
                if ($client->resourceId === $receiverId) {
                    dump('Send status ' . $responseData['status'] . ' to socket ' . $receiverId);
                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                }
            }
        }

        if ($data['status'] === 201) {

            $receiver = array_filter($this->audioClients, function ($id) use ($data) {
                return $id != $data['sender_id'];
            }, ARRAY_FILTER_USE_KEY);

            $receiver = array_values($receiver);

            $responseData = [
                "type" => 'call',
                "sender_id" =>  $this->getSocketIdByUser($data['sender_id']),
                "receiver_id" => $receiver[0],
                "voice_audio" => $data['voice_audio']
            ];

//            foreach ($this->clients as $client) {
//                if ($client->resourceId === $this->receiverSocketId) {
//                    dump('Sending voice from ' . $responseData['receiver_id'] . ' to ' . $responseData['sender_id']);
//                    $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
//                }
//            }
            $this->sendVoiceCall($responseData);
        }

        if ($data['status'] == 400) {
            $responseData = [
                "status" => $data['status']
            ];

            dump('Status ' . $data['status']);

            foreach ($this->clients as $client) {
                foreach ($this->audioClients as $receiver) {
                    if ($client->resourceId == $receiver) {
                        dump('Close call ' . $receiver);
                        $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
                    }
                }
            }
        }
    }

    public function sendVoiceCall($data)
    {
//        $receiverId = $this->getSocketIdByReceiver($data['receiver_id']);
        $receiverId = $data['receiver_id'];

        $responseData = [
            'type' => $data['type'],
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'voice_audio' => $data['voice_audio']
        ];

        foreach ($this->clients as $client) {
            if ($client->resourceId === $receiverId) {
                dump('Sending voice from ' . $responseData['receiver_id'] . ' to ' . $responseData['sender_id']);
                $client->send(json_encode($responseData, JSON_THROW_ON_ERROR));
            }
        }
    }
}

