<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Http\JsonResponse;
use App\Models\Call;

class CallController extends Controller
{
    private UserQueries $userRepository;

    public function __construct(UserQueries $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function list(int $userId): JsonResponse
    {
        $calls =  Call::forUser($userId)->latest()->get();

        $calls = $this->mapCalls($calls, $userId);

        return response()->json(['status' => true, 'calls' => $calls])
            ->setStatusCode(200);
    }

    public function listGtId($id, $userId): JsonResponse
    {
        $calls = Call::greatThen($id, $userId)->get();

        $calls = $this->mapCalls($calls, $userId);

        return response()->json(['status' => true, 'calls' => $calls])
            ->setStatusCode(200);
    }

    private function mapCalls($calls, int $userId)
    {
        return $calls->map(function ($call) use ($userId) {
            /** @var Call $call */
            return [
                'id' => $call->id,
                'sender_id' => $call->sender_id,
                'receiver_id' => $call->receiver_id,
                'status' => $call->status,
                'duration' => $call->duration,
                'created_at' => $call->created_at,
                'updated_at' => $call->updated_at,
                'caller' => [
                    'id' => $call->sender_id == $userId ?
                        $call->caller->id : $this->userRepository->getById($call->sender_id)->id,
                    'username' => $call->sender_id == $userId ?
                        $call->caller->username : $this->userRepository->getUsernameById($call->sender_id)
                ]
            ];
        });
    }

}
