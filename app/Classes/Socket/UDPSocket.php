<?php


namespace App\Classes\Socket;


use Exception;
use JsonException;
use React\Datagram\Factory;
use React\Datagram\Socket;
use React\EventLoop\LoopInterface;


class UDPSocket
{
    /**
     * @var string
     */
    protected string $address;

    /**
     * @var LoopInterface
     */
    protected LoopInterface $loop;

    /**
     * @var array
     */
    protected array $clients = [];

    /**
     * @var Socket
     */
    protected Socket $socket;

    public static array $members = [];

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
     * @throws Exception
     */
    public function event($data, $address): void
    {
        $receiveAddress = null;

        if (str_contains($data, 'sender_id')){
            $ids = (int)preg_replace('/\D/', '', $data);
            $voicesId = substr($ids,0,1);
            $callId = substr($ids,1);

            dump($data);

            $user = \DB::table('users')
                ->select('username','call_address')
                ->where('id', [$voicesId])
                ->first();

//            dump('Call_adress '. $user->call_address);

            $this->addClient($user->username, $user->call_address);
        } else {
//            dump('Adress' . $address);
            $this->sendMessage($data, $address);
        }

    }

    protected function addClient($userId, $address)
    {
        if (array_key_exists($address, $this->clients)) {
            return;
        }

        $this->clients[$address] = $userId;

//        $this->broadcast("$name enters chat", $address);
    }

    protected function removeClient($address)
    {
        $name = $this->clients[$address] ?? '';

        unset($this->clients[$address]);

        $this->broadcast("$name leaves chat");
    }

    protected function broadcast($data, $except = null)
    {
        dump($data);

        foreach ($this->clients as $address => $userId) {
            if ($address === $except) {
                continue;
            }

            $this->socket->send($data, $address);
        }
    }

    protected function sendMessage($data, $address)
    {
        $name = $this->clients[$address] ?? '';

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

