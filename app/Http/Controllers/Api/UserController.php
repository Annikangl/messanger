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
    private UserQueries $userQueries;

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
