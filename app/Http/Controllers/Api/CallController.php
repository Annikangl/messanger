<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CallQueries;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use App\Models\Call;

class CallController extends Controller
{
    private UserQueries $userRepository;
    private CallQueries $callRepository;

    public function __construct(UserQueries $userRepository, CallQueries $callRepository)
    {
        $this->userRepository = $userRepository;
        $this->callRepository = $callRepository;
    }

    public function list(int $userId): JsonResponse
    {
        $calls =  $this->callRepository->getByUser($userId);

        $calls = $this->mapCalls($calls, $userId);

        return response()->json(['status' => true, 'calls' => $calls])
            ->setStatusCode(200);
    }

    public function listGtId($id, $userId): JsonResponse
    {
        $calls = $this->callRepository->getByUserGreatThen($userId, $id);

        $calls = $this->mapCalls($calls, $userId);

        return response()->json(['status' => true, 'calls' => $calls])
            ->setStatusCode(200);
    }

    private function mapCalls(Collection $calls, int $userId): Collection|\Illuminate\Support\Collection
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
