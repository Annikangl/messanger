<?php


namespace App\Repositories;


use App\Models\Friends;
use App\Models\User;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class EloquentUserQueries implements UserQueries
{

    public function getById(int $id): User
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
        dd(1);
        $result = User::find($id)
            ->select('username')
            ->first()
            ->value('username');

        return $result;

    }
}
