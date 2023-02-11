<?php

namespace App\Observers;

use App\Models\User;
use App\Repositories\EloquentUserQueries;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        \Cache::tags('user')->flush();
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        $key = EloquentUserQueries::class . 'user_' . $user->id . '_socketId';
        \Cache::forget($key);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        \Cache::forget(EloquentUserQueries::class . '_users');
    }

}
