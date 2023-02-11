<?php

namespace App\Observers;

use App\Models\ChatRoom;

class ChatRoomObserver
{
    /**
     * Handle the ChatRoom "created" event.
     *
     * @param  \App\Models\ChatRoom  $chatRoom
     * @return void
     */
    public function created(ChatRoom $chatRoom)
    {
        \Cache::tags('chatrooms')->flush();
    }

    /**
     * Handle the ChatRoom "updated" event.
     *
     * @param  \App\Models\ChatRoom  $chatRoom
     * @return void
     */
    public function updated(ChatRoom $chatRoom)
    {
        \Cache::tags('chatrooms')->flush();
    }

    /**
     * Handle the ChatRoom "deleted" event.
     *
     * @param  \App\Models\ChatRoom  $chatRoom
     * @return void
     */
    public function deleted(ChatRoom $chatRoom)
    {
        \Cache::tags('chatrooms')->flush();
    }
}
