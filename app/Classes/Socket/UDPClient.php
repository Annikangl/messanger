<?php


namespace App\Classes\Socket;


use React\Datagram\Factory;
use React\Datagram\Socket;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\ReadableStreamInterface;

class UDPClient
{
    /** @var  LoopInterface */
    protected $loop;

    /** @var string */
    private $address;

    /** @var ReadableStreamInterface */
    protected $stdin;

    /** @var  Socket */
    protected $socket;

    /** @var string */
    protected $name = '';


    public function __construct($address, LoopInterface $loop)
    {
        $this->address = $address;
        $this->loop = $loop;
    }

    public function run()
    {
        $factory = new Factory($this->loop);
        $this->stdin = new ReadableResourceStream(STDIN, $this->loop);
        $this->stdin->on('data', [$this, 'processInput']);

        $factory->createClient($this->address)
            ->then(
                [$this, 'initClient'],
                function (\Exception $error) {
                    echo "ERROR: {$error->getMessage()}\n";
                }
            );

        $this->loop->run();
    }

    public function initClient(Socket $client)
    {
        $this->socket = $client;

        $this->socket->on(
            'message', function ($message) {
            echo $message . "\n";
        }
        );

        $this->socket->on(
            'close', function () {
            $this->loop->stop();
        }
        );

        echo "Enter your name: ";
    }

    public function processInput($data)
    {
        $data = trim($data);

        if (empty($this->name)) {
            $this->name = $data;
            $this->sendData('', 'enter');
            return;
        }

        if ($data == ':exit') {
            $this->sendData('', 'leave');
            $this->socket->end();
            return;
        }

        $this->sendData($data);
    }

    protected function sendData($message, $type = 'message')
    {
        $data = [
            'type'    => $type,
            'name'    => $this->name,
            'message' => $message,
        ];

        $this->socket->send(json_encode($data));
    }
}
