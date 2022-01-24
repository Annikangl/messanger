<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\UserNotFoundExceprion;
use App\Http\Controllers\Controller;
use App\Models\Friends;
use App\Models\User;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $userQueries;

    public function __construct(UserQueries $userQueries)
    {
        $this->userQueries = $userQueries;
    }

    /**
     * @throws UserNotFoundExceprion
     */
    public function show(int $id)
    {
        $user = $this->userQueries->getById($id);

        if (!$user) {
            throw new UserNotFoundExceprion('User not found');
        }

        return response()->json([
            "status" => true,
            "user" => $user
        ])->setStatusCode(200);
    }

    /**
     * Get user friends by userId
     * @throws UserNotFoundExceprion
     */
    public function friends(int $id): \Illuminate\Http\JsonResponse
    {

        $friends = $this->userQueries->getUserFriends($id);

//        $friends = DB::table('users_friends')
//                        ->join('users', 'users_friends.friend_id', 'users.id')
//                        ->select('users_friends.friend_id as id','users.username', 'users.email', 'users.avatar', 'users.last_login')
//                        ->where('users_friends.user_id', $id)
//                        ->get();

        if ($friends->isEmpty()) {
            throw new UserNotFoundExceprion('Friends not found');
        }

        return response()->json([
            "status" => true,
            "friends" => $friends
        ])->setStatusCode(200);
    }

    /**
     * @throws UserNotFoundExceprion
     */
    public function searchUser(int $userId, string $username): \Illuminate\Http\JsonResponse
    {
        $user = $this->userQueries->getByUsername($username, $userId);

        if ($user->isEmpty()) {
            throw new UserNotFoundExceprion('User not found by username ' . $username);
        }

        return response()->json([
            "status" => 200,
            "user" => $user
        ]);

    }
}
