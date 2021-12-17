<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Env;
use React\EventLoop\Loop;

class UdpClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'udpclient:start';

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
        $this->info('Start UDP server on ' . Env::get('UDP_SOCKET_URL') . ' port ' . 1234);
        $loop_client = Loop::get();
        (new \App\Classes\Socket\UDPClient('192.168.0.105:1234', $loop_client))->run();
    }
}
