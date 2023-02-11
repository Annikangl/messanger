<?php

namespace App\Jobs\Message;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class SaveAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $message;
    private string $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message, $path)
    {
        $this->message = $message;
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $content = base64_decode($this->message);
        try {
            Storage::disk('user_files')->put($this->path, $content);
        } catch (FileException) {
            throw new FileException('Aduio message can`t saved');
        }
    }
}
