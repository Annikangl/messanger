<?php

namespace App\Providers;

use App\Repositories\EloquentChatRoomQueries;
use App\Repositories\EloquentMessageQueries;
use App\Repositories\EloquentUserQueries;
use App\Repositories\Interfaces\ChatRoomQueries;
use App\Repositories\Interfaces\MessageQueries;
use App\Repositories\Interfaces\UserQueries;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            UserQueries::class,
            EloquentUserQueries::class,
        );

        $this->app->bind(
            ChatRoomQueries::class,
            EloquentChatRoomQueries::class
        );

        $this->app->bind(
            MessageQueries::class,
            EloquentMessageQueries::class
        );

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
