<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    private UserQueries $userRepository;

    public function __construct(UserQueries $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index($id): \Illuminate\Http\JsonResponse
    {
        $users = $this->userRepository->getAll($id);

        return response()->json([
            'status' => true,
            'users' => $users
        ]);

//        return new UserCollection($users);
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $user = $this->userRepository->getById($id);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return response()->json(['status ' => true, 'user' => $user])
            ->setStatusCode(200);
    }

    public function searchUser(int $userId, string $username): \Illuminate\Http\JsonResponse
    {
        $user = $this->userRepository->getByUsername($username, $userId);

        if ($user->isEmpty()) {
            throw new NotFoundException('User not found by username ' . $username);
        }

        return response()->json(['status ' => true, 'user' => $user])
            ->setStatusCode(200);
    }
}
