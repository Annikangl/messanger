<?php

namespace App\Observers;

use App\Models\Message;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     *
     * @param Message $message
     * @return void
     */
    public function saving(Message $message)
    {
        $this->clearCache($message);
    }

    public function saved(Message $message)
    {
        $this->clearCache($message);
    }

    private function clearCache(Message $message)
    {
        $key_1 = 'chatrooms_by_user_' . $message->sender_id;
        $key_2 = 'chatrooms_by_user_' . $message->receiver_id;
        $key_3 = 'chatrooms_by_user_' . $message->sender_id . '_gt';
        $key_4 = 'chatrooms_by_user_' . $message->receiver_id . '_gt';

        \Cache::forget($key_1);
        \Cache::forget($key_2);
        \Cache::forget($key_3);
        \Cache::forget($key_4);
    }
}
