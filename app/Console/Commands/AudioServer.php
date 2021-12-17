<?php

namespace App\Console\Commands;


use App\Classes\Socket\UDPClient;
use App\Classes\Socket\UDPSocket;
use Illuminate\Console\Command;
use Illuminate\Support\Env;
use React\Datagram\Factory;
use React\EventLoop\Loop;

class AudioServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'udpsocket:serve';
    private $port = 1234;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $address = Env::get('UDP_SOCKET_URL')  . ':' . $this->port;
        $this->info('Start UDP server on ' . Env::get('UDP_SOCKET_URL') . ' port ' . $this->port);

        $loop = Loop::get();
        (new UDPSocket($address, $loop))->run();



    }
}
