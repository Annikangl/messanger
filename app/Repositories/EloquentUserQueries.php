<?php


namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EloquentUserQueries implements UserQueries
{
    public function getAll($id): Collection|array
    {
        return User::select('id','username')->where('id','<>', $id)->get();
    }

    public function getById(int $id): Model|Collection|array|User|null
    {
        return User::find($id);
    }

    public function getByUsername(string $username, int $userId): Collection
    {
        $result = User::select('id','username','avatar','active','last_login')
            ->where('username', 'LIKE','%'.$username.'%')
            ->where('id','<>',[$userId])
            ->get();

        return $result;
    }

    public function getUserFriends($userId): \Illuminate\Support\Collection
    {
        $result = DB::table('users_friends')
                        ->join('users', 'users_friends.friend_id', 'users.id')
                        ->select('users_friends.friend_id as id','users.username', 'users.email',
                            'users.avatar', 'users.last_login')
                        ->where('users_friends.user_id', $userId)
                        ->get();

        return $result;
    }

    public function getUsernameById(int $id): string
    {
        return User::query()->select('username')->where('id', $id)->value('username');
    }

    public function chatroomByUser(int $id)
    {
        return User::query()->find($id)->chatRooms->pluck('id');
    }

    public function getByEmail(string $email): Model|User|null
    {
        return User::query()->where('email', $email)->first();
    }

    public function getSocketIdByChatRoom(int $senderId, int $receiverId): Collection|array
    {
        return User::query()->select('socket_id')->whereIn('id', [$senderId, $receiverId])->get();
    }
}
