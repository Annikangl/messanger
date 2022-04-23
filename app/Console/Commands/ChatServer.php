<?php

namespace App\Console\Commands;

use App\Classes\Socket\ChatSocket;
use App\Http\UseCases\User\UserService;
use http\Env\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Env;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class ChatServer extends Command
{

    protected $signature = 'websocket:serve';
    protected $description = 'Initializing Websocket server to send and receive messages';
    private int $port = 6001;
    private UserService $userService;


    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    public function handle()
    {
        $this->info('Start websocket server on ' . Env::get('SOCKET_URL') . ' port ' . $this->port);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ChatSocket($this->userService)
                )
            ),
            $this->port
        );

        $server->run();

        return 0;
    }
}
