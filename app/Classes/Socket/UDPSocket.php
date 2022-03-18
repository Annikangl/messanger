<?php


namespace App\Classes\Socket;


use App\Models\Call;
use App\Models\User;
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

        if (str_contains($data, 'sender_id')){
            $ids = (int)preg_replace('/\D/', '', $data);
            $voicesId = substr($ids,0,1);
            $callId = substr($ids,1);

            $callRoom = Call::where('id',[$callId])->first();

            $callAdresses = User::whereIn('call_address',[$callRoom->sender_id,$callRoom->receiver_id])->get();

            dump($callAdresses);

//            $user = \DB::table('users')
//                ->select('username','call_address')
//                ->where('id', [$voicesId])
//                ->first();

//            dump('Call_adress '. $user->call_address);

            $this->addClient($callRoom->id, $callRoom);
        } else {
            $this->sendMessage($data, $address);
        }

    }

    protected function addClient(int $callId, array $addresses)
    {
        if (array_key_exists($callId, $this->clients)) {
            return;
        }

        $this->clients[$callId] = $addresses;

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
        dump('address', $except);

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

