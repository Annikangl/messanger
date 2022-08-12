<?php

namespace App\Exceptions\ChatRoom;

use Exception;

class ChatRoomException extends Exception
{
    public function render()
    {
        return response()->json(['status' => false, 'error' => $this->getMessage()]);
    }
}
