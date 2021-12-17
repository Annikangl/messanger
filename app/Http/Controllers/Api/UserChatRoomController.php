<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserChatRoom;

class UserChatRoomController extends Controller
{
    public function store($request)
    {
        $request_data = [
            [
                'user_id' => $request['sender_id'],
                'chat_room_id' => $request['chat_room_id']
            ],
            [
                'user_id' => $request['receiver_id'],
                'chat_room_id' => $request['chat_room_id']
            ]
        ];

        return UserChatRoom::insert($request_data);
    }
}
