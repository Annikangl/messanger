<?php


namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EloquentUserQueries implements UserQueries
{
    public function getAll($id): Collection|array
    {
        $key = __CLASS__ . '_users';

        $result = \Cache::tags('user')->remember($key, 60*60*24, function () use ($id) {
            return User::select('id','username')->where('id','<>', $id)->get();
        });

        return $result;
    }

    public function getById(int $id): User
    {
        $key = __CLASS__ . 'user_' . $id;

        $result = \Cache::tags('user')->remember($key, 60*60*24, function () use ($id) {
            return User::query()->find($id);
        });

        return $result;
    }

    public function getByUsername(string $username, int $userId): Collection
    {
        $result = User::select('id','username','avatar','active','last_login')
            ->where('username', 'LIKE','%'.$username.'%')
            ->where('id','<>',[$userId])
            ->get();

        return $result;
    }

    public function getUsernameById(int $id): ?string
    {
        $key = __CLASS__ . 'user_' . $id . '_username';

        $result = \Cache::tags('user')->remember($key, 60*60*24, function () use ($id) {
            return User::query()->select('username')->where('id', $id)->value('username');
        });

        return $result;
    }

    public function chatroomByUser(int $id)
    {
        $key = __CLASS__ . 'user_' . $id . '_chatRoom';

        $result = \Cache::tags('user')->remember($key, 60*10, function () use ($id) {
            return User::query()->find($id)->chatRooms->pluck('id');
        });

        return $result;
    }

    public function getByEmail(string $email): Model|User|null
    {
        return User::query()->where('email', $email)->first();
    }

    public function getSocketIdByChatRoom(int $senderId, int $receiverId): Collection|array
    {
        $key = __CLASS__ . 'users_' . $senderId . '_' . $receiverId;

        $result = \Cache::tags('user')->remember($key, 60*2, function () use ($senderId, $receiverId) {
            return User::query()->select('socket_id')->whereIn('id', [$senderId, $receiverId])->get();
        });

        return $result;
    }

    public function getSocketId(int $id): int
    {
        $key = __CLASS__ . 'user_' . $id . '_socketId';

        $result = \Cache::tags('user')->remember($key, 60^5, function () use ($id) {
            return User::query()->where('id', $id)->value('socket_id');
        });

//        $result =  User::query()->where('id', $id)->value('socket_id');
        return $result;
    }
}
