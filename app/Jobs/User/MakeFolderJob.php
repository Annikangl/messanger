<?php

namespace App\Jobs\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MakeFolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задания.
     *
     * @var int
     */
    public int $tries = 3;

    protected User $user;
    protected string $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $path)
    {
        $this->user = $user->withoutRelations();
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!Storage::disk('local')->exists($this->path)) {
            Storage::disk('local')->makeDirectory($this->path);
        }

        $this->user->folder = $this->path;
        $this->user->save();
    }
}
