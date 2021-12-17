<?php

namespace App\Console\Commands;

use App\Classes\Socket\ChatSocket;
use http\Env\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Env;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class ChatServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:serve';
    private $port = 6001;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializing Websocket server to receive and manage connections';

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
        $this->info('Start websocket server on ' . Env::get('SOCKET_URL') . ' port ' . $this->port);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ChatSocket()
                )
            ),
            $this->port
        );

        $server->run();
    }
}
