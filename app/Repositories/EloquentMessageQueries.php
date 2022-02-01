<?php


namespace App\Repositories;


use App\Models\ChatRoom;
use App\Repositories\Interfaces\MessageQueries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

class EloquentMessageQueries implements MessageQueries
{

    public function getWithPaginate(int $chatRoomId, $perPage): Paginator
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.id as message_id','messages.sender_id as sender_id', 'messages.message','messages.audio','messages.created_at')
            ->latest('messages.created_at')
            ->simplePaginate($perPage);

        return $result;
    }

    public function getWithoutPaginate(int $chatRoomId): Collection
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.sender_id as sender_id', 'messages.message','messages.audio','messages.created_at')
            ->latest('messages.created_at')
            ->get();

        return $result;
    }

    public function getNewMessage(int $chatRoomId, int $messageId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.id as message_id','messages.sender_id as sender_id', 'messages.message','messages.audio','messages.created_at')
            ->where('messages.id', '>', [$messageId])
            ->latest('messages.created_at')
            ->get();

        return $result;
    }

    public function getOldMessage(int $chatRoomId, int $messageId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.id as message_id','messages.sender_id as sender_id', 'messages.message','messages.audio','messages.created_at')
            ->where('messages.id', '<', [$messageId])
            ->latest('messages.created_at')
            ->limit(15)
            ->get();

        return $result;
    }



}
