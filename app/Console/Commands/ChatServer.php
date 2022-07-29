<?php

namespace App\Console\Commands;

use App\Classes\Socket\ChatSocket;
use App\Http\UseCases\Call\AudioCallService;
use App\Http\UseCases\Messages\MessagesService;
use App\Http\UseCases\User\UserService;
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
    private AudioCallService $callService;
    private MessagesService $messagesService;


    public function __construct(UserService $userService, AudioCallService $callService, MessagesService $messagesService)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->callService = $callService;
        $this->messagesService = $messagesService;
    }

    public function handle()
    {
        $this->info('Start websocket server on ' . Env::get('SOCKET_URL') . ' port ' . $this->port);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ChatSocket($this->userService, $this->callService, $this->messagesService)
                )
            ),
            $this->port
        );

        $server->run();

        return 0;
    }
}
