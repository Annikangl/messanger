<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Message\File;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private UserQueries $userRepository;

    public function __construct(UserQueries $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index($id): JsonResponse
    {
        $users = $this->userRepository->getAll($id);

        return response()->json([
            'status' => true,
            'users' => $users
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->getById($id);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return response()->json(['status ' => true, 'user' => $user])
            ->setStatusCode(200);
    }

    public function showFileList(int $userId): JsonResponse
    {
        $files = $this->userRepository->getFileList($userId);

        $data = collect();

        $files->each(function ($value) use (&$data) {
            /** @var File $value */
            $value->text_size = $value->calculateMegabytes();
            $data->push($value);
        });

        return response()->json(['status' => true, 'files' => $data])
            ->setStatusCode(200);
    }

    public function searchUser(int $userId, string $username): JsonResponse
    {
        $user = $this->userRepository->getByUsername($username, $userId);

        if ($user->isEmpty()) {
            throw new NotFoundException('User not found by username ' . $username);
        }

        return response()->json(['status ' => true, 'user' => $user])
            ->setStatusCode(200);
    }
}
