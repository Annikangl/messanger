<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Friends;
use App\Models\User;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private UserQueries $userRepository;

    public function __construct(UserQueries $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $users = $this->userRepository->getAll();

        return response()->json([
            'status' => true,
            'users' => $users
        ]);
    }

    public function show(int $id)
    {
        $user = $this->userRepository->getById($id);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return response()->json(["status" => true, "user" => $user])
            ->setStatusCode(200);
    }

    /**
     * Get user friends by userId
     */
    public function friends(int $id): \Illuminate\Http\JsonResponse
    {
        $friends = $this->userRepository->getUserFriends($id);

        if ($friends->isEmpty()) {
            throw new NotFoundException('Friends not found');
        }

        return response()->json([
            "status" => true,
            "friends" => $friends
        ])->setStatusCode(200);
    }

    public function searchUser(int $userId, string $username): \Illuminate\Http\JsonResponse
    {
        $user = $this->userRepository->getByUsername($username, $userId);

        if ($user->isEmpty()) {
            throw new NotFoundException('User not found by username ' . $username);
        }

        return response()->json(["status" => 200, "user" => $user])
            ->setStatusCode(200);

    }
}
