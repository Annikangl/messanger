<?php


namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EloquentUserQueries implements UserQueries
{
    public function getAll($id): Collection
    {
        return User::select('id','username','updated_at')->where('id','<>', $id)->get();
    }

    public function getById(int $id): User
    {
        $key = __CLASS__ . 'user_' . $id;

        $result = \Cache::tags('user')->remember($key, 60*60*24, function () use ($id) {
            return User::query()->find($id);
        });

        return $result;
    }

    public function getFileList(int $userId): Collection
    {
        $user = User::query()->find($userId);
        return $user->files()
            ->select('message_files.id','message_files.message_id', 'message_files.filename','message_files.extension','message_files.size')
            ->get();
    }

    public function getByUsername(string $username, int $userId): Collection
    {
        return User::select('id','username','avatar','active','last_login')
            ->where('username', 'LIKE','%'.$username.'%')
            ->where('id','<>',[$userId])
            ->get();
    }

    public function getUsernameById(int $id): ?string
    {
        $key = __CLASS__ . 'user_' . $id . '_username';

        $result = \Cache::tags('user')->remember($key, 60*60*24, function () use ($id) {
            return User::query()->select('username')->where('id', $id)->value('username');
        });

        return $result;
    }

    public function chatroomByUser(int $id): \Illuminate\Support\Collection
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

        return $result;
    }

    public function getOfflineUsers(): Collection
    {
        return User::query()->select('id','username','active')
            ->where('active', User::STATUS_OFFLINE)->get();
    }

    public function getUsersWithActive(): Collection
    {
        return User::query()->select('id','username','active','updated_at')->get();
    }

    public function getOnlineUsers(): Collection
    {
        return User::query()->select('id','username','active','updated_at')
            ->where('active', User::STATUS_ONLINE)->get();
    }
}
