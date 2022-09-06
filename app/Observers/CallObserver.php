<?php

namespace App\Observers;

use App\Models\Call;

class CallObserver
{
    /**
     * Handle the Call "created" event.
     *
     * @param  \App\Models\Call  $call
     * @return void
     */
    public function created(Call $call)
    {
        \Cache::tags('calls')->flush();
    }

    /**
     * Handle the Call "updated" event.
     *
     * @param  \App\Models\Call  $call
     * @return void
     */
    public function updated(Call $call)
    {
        \Cache::tags('calls')->flush();
    }
}
