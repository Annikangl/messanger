<?php


namespace App\Repositories;

use App\Models\ChatRoom;
use App\Models\User;
use App\Repositories\Interfaces\ChatRoomQueries;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentChatRoomQueries implements ChatRoomQueries
{
    public function getListByUser(int $userId): Collection
    {
        $key = 'chatrooms_bu_user_' . $userId;

        $result = \Cache::tags('chatrooms')->remember($key, 60*5, function () use ($userId) {
            return DB::table('chat_room_user')
                ->join('chat_rooms', 'chat_room_user.chat_room_id', 'chat_rooms.id')
                ->join('users', 'chat_room_user.user_id', 'users.id')
                ->join('messages', 'messages.chat_room_id', 'chat_rooms.id')
                ->select('chat_rooms.id','users.username AS title','messages.id AS message_id',
                    'messages.message AS last_message','messages.updated_at')
                ->where('users.id','<>', $userId)
                ->whereIn('chat_rooms.id', function (Builder $query) use ($userId) {
                    $query->select('chat_room_id')->from('chat_room_user')
                        ->where('user_id', $userId);
                })
                ->whereIn('messages.id', function (Builder $query) {
                    $query->selectRaw('MAX(messages.id)')->from('messages')
                        ->whereNull('deleted_at')
                        ->groupBy('messages.chat_room_id');
                })
                ->orderByDesc('messages.created_at')
                ->get();
        });

        return $result;
    }

    /**
     * @param int $chatRoomId
     * @param int $userId
     * @return Collection
     * Get chat rooms by user great then $chatRoomId
     */
    public function getListByUserGtId(int $chatRoomId, int $userId): Collection
    {
        $key = 'chatrooms_bu_user_' . $userId . '_gt';

        $result = \Cache::tags('chatrooms')->remember($key, 60*5, function () use ($chatRoomId, $userId) {
           return DB::table('chat_room_user')
               ->join('chat_rooms', 'chat_room_user.chat_room_id', 'chat_rooms.id')
               ->join('users', 'chat_room_user.user_id', 'users.id')
               ->join('messages', 'messages.chat_room_id', 'chat_rooms.id')
               ->select('chat_rooms.id','users.username AS title','messages.id AS message_id',
                   'messages.message AS last_message','messages.updated_at')
               ->where('users.id','<>', $userId)
               ->whereIn('chat_rooms.id', function (Builder $query) use ($userId) {
                   $query->select('chat_room_id')->from('chat_room_user')
                       ->where('user_id', $userId);
               })
               ->where('chat_rooms.id', '>', $chatRoomId)
               ->whereIn('messages.id', function (Builder $query) {
                   $query->selectRaw('MAX(messages.id)')
                       ->from('messages')
                       ->whereNull('deleted_at')
                       ->groupBy('messages.chat_room_id');
               })
               ->orderByDesc('messages.created_at')
               ->get();
        });

        return $result;
    }

    public function getUser(int $id): User|\Illuminate\Database\Eloquent\Builder
    {
        return User::query()->find($id);
    }

    public function getTitleByUserId(int $userId, int $chatRoomId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->users()
            ->select('username')
            ->where('user_id','<>', $userId)
            ->value('username');

        return $result;
    }

    public function getReceiverByChatRoom($chatRoomId, $userId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->users()
            ->where('user_id','<>', $userId)
            ->value('user_id');

        return $result;
    }
}
