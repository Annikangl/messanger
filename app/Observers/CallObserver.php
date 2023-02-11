<?php

namespace App\Observers;

use App\Models\Call;
use App\Repositories\EloquentCallQueries;

class CallObserver
{
    /**
     * Handle the Call "created" event.
     *
     * @param Call $call
     * @return void
     */
    public function created(Call $call)
    {
        $this->clearCache($call);
    }

    /**
     * Handle the Call "updated" event.
     *
     * @param Call $call
     * @return void
     */
    public function updated(Call $call)
    {
        $this->clearCache($call);
    }

    private function clearCache(Call $call)
    {
        $key = EloquentCallQueries::class . '_user_' . $call->sender_id . '_calls';
        $key_2 = EloquentCallQueries::class . '_user_' . $call->receiver_id . '_calls';
        $key_3 = EloquentCallQueries::class . '_user_' . $call->sender_id .'_calls_gt';
        $key_4 = EloquentCallQueries::class . '_user_' . $call->receiver_id .'_calls_gt';
        \Cache::forget($key);
        \Cache::forget($key_2);
        \Cache::forget($key_3);
        \Cache::forget($key_4);
    }
}
