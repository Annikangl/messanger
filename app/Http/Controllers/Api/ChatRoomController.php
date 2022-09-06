<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatroomRequest;
use App\Http\UseCases\ChatRoomService\ChatRoomService;
use App\Repositories\Interfaces\ChatRoomQueries;
use Illuminate\Http\JsonResponse;

class ChatRoomController extends Controller
{
    private ChatRoomQueries $chatRoomRepository;
    private ChatRoomService $chatRoomService;

    public function __construct(ChatRoomQueries $chatRoomRepository, ChatRoomService $chatRoomService)
    {
        $this->chatRoomRepository = $chatRoomRepository;
        $this->chatRoomService = $chatRoomService;
    }

    /**
     * Get chat rooms by user
     * @param int $userId
     * @return JsonResponse
     */
    public function listByUser(int $userId): JsonResponse
    {
        $chatRooms = $this->chatRoomRepository->getListByUser($userId);

        return response()->json(["status" => true, "chat_rooms" => $chatRooms])
            ->setStatusCode(200);
    }

    /**
     * Get chat rooms by user great then $chatRoomId
     * @param int $chatRoomId
     * @param int $userId
     * @return JsonResponse
     */
    public function listByUserGtId(int $chatRoomId, int $userId): JsonResponse
    {
        $chatRooms = $this->chatRoomRepository->getListByUserGtId($chatRoomId, $userId);

        return response()->json(["status" => true, "chat_rooms" => $chatRooms])
            ->setStatusCode(200);
    }

    /**
     * Create new chat room or return exist
     * @param StoreChatroomRequest $request
     * @return JsonResponse
     */
    public function store(StoreChatroomRequest $request): JsonResponse
    {
        try {
            $chatRoom = $this->chatRoomService->create(
                $request['title'],
                $request['sender_id'],
                $request['receiver_id']
            );
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'error' => $exception->getMessage()]);
        }

        return response()->json(["status" => true, "chat_room_id" => $chatRoom])
            ->setStatusCode(201);
    }
}
