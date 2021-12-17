<?php


namespace App\Classes\Socket;


use Illuminate\Support\Env;
use React\Datagram\Factory;
use React\Datagram\Socket;
use React\EventLoop\LoopInterface;


class UDPSocket
{
    /**
     * @var string
     */
    protected $address;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var array
     */
    protected $clients = [];

    /**
     * @var Socket
     */
    protected $socket;

    /**
     * @param string $address
     * @param LoopInterface $loop
     */
    public function __construct(string $address, LoopInterface $loop)
    {
        $this->address = $address;
        $this->loop = $loop;
    }

    /**
     * @throws \JsonException
     */
    public function event($data, $address)
    {
       if (str_contains($data, 'type')) {
           // TODO convert to associative array
           $data = json_decode($data, true);
           dump('Array ' , $data);
       } else {
           dump('Bytes ', $data);
       }

        $this->addClient('test',$address);
        $this->sendMessage($data, $address);
//        $data = json_decode($data, true);
//
//        if ($data['type'] == 'enter') {
//            $this->addClient($data['name'], $address);
//            return;
//        }
//
//        if ($data['type'] == 'leave') {
//            $this->removeClient($address);
//            return;
//        }
//
//        $this->sendMessage($data['message'], $address);
    }

    protected function addClient($name, $address)
    {
        if (array_key_exists($address, $this->clients)) {
            return;
        }

        $this->clients[$address] = $name;

        $this->broadcast("$name enters chat", $address);
    }

    protected function removeClient($address)
    {
        $name = $this->clients[$address] ?? '';

        unset($this->clients[$address]);

        $this->broadcast("$name leaves chat");
    }

    protected function broadcast($message, $except = null)
    {
        foreach ($this->clients as $address => $name) {
            if ($address == $except) continue;

            $this->socket->send($message,  $address);
        }
    }

    protected function sendMessage($message, $address)
    {
        $name = $this->clients[$address] ?? '';

        $this->broadcast($message, $address);
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
                function (\Exception $error) {
                    echo "ERROR: {$error->getMessage()}\n";
                }
            );

        $this->loop->run();
    }

}

