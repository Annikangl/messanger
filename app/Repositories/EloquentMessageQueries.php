<?php


namespace App\Repositories;


use App\Models\ChatRoom;
use App\Repositories\Interfaces\MessageQueries;

class EloquentMessageQueries implements MessageQueries
{

    public function getWithPaginate(int $chatRoomId, $perPage)
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.sender_id as sender_id', 'messages.message','messages.audio','messages.created_at')
            ->latest('messages.created_at')
            ->simplePaginate($perPage);

//        $result = DB::table('messages')
//            ->join('chat_rooms','messages.chat_room_id','chat_rooms.id')
//            ->select('messages.sender_id as sender_id', 'messages.message','messages.audio',
//                DB::raw("DATE_FORMAT(messages.created_at, '%h:%i') as created_at"))
//            ->where('messages.chat_room_id', [$chatRoomId])
//            ->latest('messages.created_at')
//            ->simplePaginate($perPage);

        return $result;
    }

    public function getWithoutPaginate(int $chatRoomId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.sender_id as sender_id', 'messages.message','messages.audio','messages.created_at')
            ->latest('messages.created_at')
            ->get();
//        $result = DB::table('messages')
//            ->join('chat_rooms','messages.chat_room_id','chat_rooms.id')
//            ->select('messages.sender_id as sender_id', 'messages.message','messages.audio',
//                DB::raw("DATE_FORMAT(messages.created_at, '%h:%i') as created_at"))
//            ->where('messages.chat_room_id', [$chatRoomId])
//            ->latest('messages.created_at')
//            ->get();

        return $result;
    }

    public function getUsernameById(int $userId)
    {
        // TODO
    }

}
