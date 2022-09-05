<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Repositories\Interfaces\UserQueries;

class UserController extends Controller
{
    private UserQueries $userRepository;

    public function __construct(UserQueries $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index($id): UserCollection
    {
        $users = $this->userRepository->getAll($id);

        return new UserCollection($users);
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
