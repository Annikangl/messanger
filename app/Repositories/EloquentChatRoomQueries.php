<?php


namespace App\Repositories;


use App\Models\ChatRoom;
use App\Models\User;
use App\Repositories\Interfaces\ChatRoomQueries;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function React\Promise\map;

class EloquentChatRoomQueries implements ChatRoomQueries
{

    public function getListByUserId($id): Collection
    {
        $result = DB::table('chat_room_user')
            ->join('chat_rooms', 'chat_room_user.chat_room_id', 'chat_rooms.id')
            ->join('users', 'chat_room_user.user_id', 'users.id')
            ->join('messages', 'messages.chat_room_id', 'chat_rooms.id')
            ->select('chat_rooms.id', 'chat_rooms.title','messages.message AS last_message','messages.updated_at')
            ->where('users.id', $id)
            ->whereIn('messages.id', function ($query) {
                $query->select(DB::raw('MAX(messages.id)'))->from('messages')->groupBy('messages.chat_room_id');
            })
            ->orderBy('messages.created_at', 'desc')
            ->get();


        return $result;
    }

    public function getByChatId(int $chatRoomId, int $userId)
    {
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        $message = $chatRoom->messages()->select('message AS last_message')->whereIn('messages.id', function (Builder $query) {
            $query->select(DB::raw('MAX(messages.id)'))->from('messages')->groupBy('messages.chat_room_id');
        })->get();

        $result = $message->map(function ($last_message) use ($chatRoom, $userId) {
            return [
                'id' => $chatRoom->id,
                'title' => $this->getTitleByUserId($userId, $chatRoom->id),
                'last_message' => $last_message->last_message,
                'updated_at' => $chatRoom->updated_at
            ];
        });

        return $result;
    }

//    Gt - great than id

    public function getGtId(int $chatRoomId, int $userId)
    {
        $chatRooms = DB::table('chat_room_user')
            ->join('chat_rooms', 'chat_room_user.chat_room_id', 'chat_rooms.id')
            ->join('users', 'chat_room_user.user_id', 'users.id')
            ->join('messages', 'messages.chat_room_id', 'chat_rooms.id')
            ->select('chat_rooms.id', 'chat_rooms.title', 'messages.message AS last_message', 'messages.updated_at')
            ->where('users.id', $userId)
            ->where('chat_rooms.id', '>', $chatRoomId)
            ->whereIn('messages.id', function ($query) {
                $query->select(DB::raw('MAX(messages.id)'))->from('messages')->groupBy('messages.chat_room_id');
            })
            ->orderByDesc('messages.created_at')
            ->get();

        return $chatRooms;
    }

    public function getByUserId(int $id): \Illuminate\Database\Eloquent\Collection
    {
        $result = User::find($id);
        return $result;
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
