<?php

namespace App\Classes\Socket;

use App\Models\Call;
use App\Models\User;
use Exception;
use React\Datagram\Factory;
use React\Datagram\Socket;
use React\EventLoop\LoopInterface;


class UDPSocket
{
    protected string $address;
    protected LoopInterface $loop;
    protected array $clients = [];
    protected $voicerAddress;
    protected array $callAddresses = [];
    protected Socket $socket;

    public function __construct(string $address, LoopInterface $loop)
    {
        $this->address = $address;
        $this->loop = $loop;
        $this->clients = [];
    }

    public function event($data, $address): void
    {
//        SELECT sender_id, receiver_id FROM `calls`
//                    WHERE (sender_id = (SELECT id FROM users WHERE call_address = '10.123.0.15:45779') OR receiver_id = (SELECT id FROM users WHERE call_address = '10.123.0.15:45779'))
//                    AND status = 200 ORDER BY created_at DESC LIMIT 1;
//        $call = Call::select('sender_id','receiver_id')
//            ->where(function ($query) use ($address) {
//                $query->where('sender_id', function ($query) use ($address) {
//                    $query->select('id')->from('users')->where('call_address', $address);
//                })->orWhere('receiver_id', function ($query) use ($address) {
//                    $query->select('id')->from('users')->where('call_address', $address);
//                });
//            })->where('status', 200)->orderByDesc('created_at')->first();

//
//        $callRoom = User::select('call_address')
//            ->whereIn('id',[$call->sender_id,$call->receiver_id])->get();
//
//        $receiver = $callRoom->filter(function ($value) use ($address) {
//            return $value->call_address != $address;
//        })->toArray();
//
//        $receiver = reset($receiver);
//
//        $this->socket->send($data, $receiver['call_address']);

        if (str_contains($data, 'sender_id')) {
            $ids = (int)preg_replace('/\D/', '', $data);
            $voicesId = substr($ids, 0, 1);
            $callRoomId = substr($ids, 0, 1);

            $callRoom = Call::find($callRoomId);

//            $clientsAddresses = User::whereIn('id', [$callRoom->sender_id, $callRoom->receiver_id])
//                ->pluck('call_address')->toArray();

            $this->addClient($callRoom->id, $address);

        } else {
            $this->sendMessage($data, $address);
        }

    }

    protected function addClient($callRoomId, $address)
    {
        if (array_key_exists($address, $this->clients)) {
            return;
        }

        $this->clients[$address] = $callRoomId;
//        if (array_key_exists($callRoomId, $this->clients)) {
//            return;
//        }
//
//        $this->clients[$callRoomId] = $address;
    }

    protected function removeClient($callRoomId)
    {
        unset($this->clients[$callRoomId]);

//        $this->broadcast("$name leaves chat");
    }

    protected function broadcast($data, $except)
    {
        foreach ($this->clients as $address => $call_id) {
            if ($address == $except) continue;
            dump('From ' . $except . ' to ' . $address);
            $this->socket->send($data, $address);
        }
//            $receiver = array_filter($addresses, function ($address) use ($except) {
//                return $address !== $except;
//            });
//
//            $receiver = reset($receiver);
//            dump('From ' . $except . ' to ' . $receiver);


//            foreach ($addresses as $address) {
//                if ($address['call_address'] === $except) {
//                    continue;
//                }
//
//                dump('send data to ' . $address['call_address'] . ' from ' . $except);
//                $this->socket->send($data, $address['call_address']);
//            }

    }

    protected function sendMessage($data, $address)
    {
        $this->broadcast($data, $address);
    }

    public function run(): void
    {
        $factory = new Factory($this->loop);
        $factory->createServer($this->address)
            ->then(
                function (Socket $server) {
                    $this->socket = $server;
                    $server->on('message', [$this, 'event']);
                },
                function (Exception $error) {
                    echo "ERROR: {$error->getMessage()}\n";
                }
            );

        $this->loop->run();
    }

}

