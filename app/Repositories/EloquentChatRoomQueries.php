<?php


namespace App\Repositories;


use App\Models\ChatRoom;
use App\Repositories\Interfaces\ChatRoomQueries;
use Illuminate\Support\Facades\DB;

class EloquentChatRoomQueries implements ChatRoomQueries
{
    private ChatRoomQueries $chatRoomQueries;

    public function getListByUserId($id): \Illuminate\Support\Collection
    {
        $result = DB::table('users_chat_rooms')
            ->join('chat_rooms', 'users_chat_rooms.chat_room_id', 'chat_rooms.id')
            ->join('users', 'users_chat_rooms.user_id', 'users.id')
            ->join('messages', 'messages.chat_room_id', 'chat_rooms.id')
            ->select('chat_rooms.id', 'chat_rooms.title','messages.message AS last_message','messages.updated_at')
            ->whereNotIn('users.username',[1,3])
            ->where('users.id', $id)
            ->whereIn('messages.id', function ($query) {
                $query->select(DB::raw('MAX(messages.id)'))->from('messages')->groupBy('messages.chat_room_id');
            })
            ->orderBy('messages.created_at', 'desc')
            ->get();

        return $result;
    }

    public function getByUserId($id)
    {
        // TODO: Implement getByUserId() method.
    }

    public function getTitleByUserId(int $userId, int $chatRoomId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->users()
            ->select('username')
            ->where('user_id','<>',[$userId])
            ->value('username');

        return $result;
    }

    public function getReceiverByChatRoom($chatRoomId, $userId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->users()
            ->where('user_id','<>',[$userId])
            ->value('user_id');

        return $result;
    }
}
