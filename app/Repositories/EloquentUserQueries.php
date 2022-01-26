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
        return User::select('id','username','avatar','active','last_login')
            ->where('username', 'LIKE','%'.$username.'%')
            ->whereNotIn('id',[$userId])
            ->get();
    }

    public function getUserFriends($userId)
    {
        return DB::table('users_friends')
                        ->join('users', 'users_friends.friend_id', 'users.id')
                        ->select('users_friends.friend_id as id','users.username', 'users.email', 'users.avatar', 'users.last_login')
                        ->where('users_friends.user_id', $userId)
                        ->get();
    }
}
