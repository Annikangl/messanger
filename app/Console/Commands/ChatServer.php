<?php

namespace App\Console\Commands;

use App\Classes\Socket\ChatSocket;
use App\Http\UseCases\Call\AudioCallService;
use App\Http\UseCases\Messages\MessagesService;
use App\Http\UseCases\User\UserService;
use App\Repositories\EloquentUserQueries;
use Illuminate\Console\Command;
use Illuminate\Support\Env;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class ChatServer extends Command
{
    protected $signature = 'websocket:serve';
    protected $description = 'Initializing Websocket server to send and receive messages';

    private int $port;
    private $socket;
    private UserService $userService;
    private AudioCallService $callService;
    private MessagesService $messagesService;
    private EloquentUserQueries $userRepository;


    public function __construct(UserService $userService, AudioCallService $callService, MessagesService $messagesService, EloquentUserQueries $userRepository)
    {
        parent::__construct();
        $this->port = \env('SOCKET_PORT', 6001);
        $this->userService = $userService;
        $this->callService = $callService;
        $this->messagesService = $messagesService;
        $this->userRepository = $userRepository;
    }

    public function handle()
    {
        $this->info('Start websocket server on ' . Env::get('SOCKET_URL') . ' port ' . $this->port);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                   new ChatSocket($this->userService, $this->callService, $this->messagesService, $this->userRepository)
                )
            ),
            $this->port
        );

        $server->run();

        return Command::SUCCESS;
    }
}
