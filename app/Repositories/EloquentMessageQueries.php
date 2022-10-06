<?php


namespace App\Repositories;

use App\Models\ChatRoom;
use App\Repositories\Interfaces\MessageQueries;
use Illuminate\Database\Eloquent\Collection;

class EloquentMessageQueries implements MessageQueries
{
    public function getPaginate(int $chatRoomId, $perPage = 15): \Illuminate\Contracts\Pagination\Paginator
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->leftJoin('message_files', 'messages.id', '=','message_files.message_id')
            ->select('messages.id as message_id','messages.sender_id','messages.receiver_id','messages.message',
                'messages.audio',
                'message_files.file',
                'messages.created_at')
            ->latest('messages.created_at')
            ->simplePaginate($perPage);

        return $result;
    }

    public function getTrashedMessages($chatRoomId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->withTrashed()
            ->whereNotNull('deleted_at')
            ->select('messages.id as message_id', 'messages.sender_id as sender_id', 'messages.message')
            ->latest('messages.deleted_at')
            ->get();

        return $result;
    }

    public function getWithoutPaginate(int $chatRoomId): Collection
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.sender_id as sender_id', 'messages.message', 'messages.audio', 'messages.created_at')
            ->latest('messages.created_at')
            ->get();

        return $result;
    }

    public function getNewMessage(int $chatRoomId, int $messageId): Collection
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.id as message_id', 'messages.sender_id as sender_id', 'messages.message', 'messages.audio', 'messages.created_at')
            ->where('messages.id', '>', $messageId)
            ->latest('messages.created_at')
            ->get();

        return $result;
    }

    public function getOldMessage(int $chatRoomId, int $messageId): Collection
    {
        $result = ChatRoom::findOrFail($chatRoomId)
            ->messages()
            ->select('messages.id as message_id', 'messages.sender_id as sender_id', 'messages.message', 'messages.audio', 'messages.created_at')
            ->where('messages.id', '<', $messageId)
            ->latest('messages.created_at')
            ->limit(15)
            ->get();

        return $result;
    }


}
