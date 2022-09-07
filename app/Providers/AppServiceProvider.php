<?php

namespace App\Providers;

use App\Models\Call;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use App\Observers\CallObserver;
use App\Observers\ChatRoomObserver;
use App\Observers\MessageObserver;
use App\Observers\UserObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();

        Message::observe(MessageObserver::class);
        Call::observe(CallObserver::class);
        User::observe(UserObserver::class);
        ChatRoom::observe(ChatRoomObserver::class);
    }
}
