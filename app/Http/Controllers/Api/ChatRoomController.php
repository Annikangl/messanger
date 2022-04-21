<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ChatNotCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatroomRequest;
use App\Models\ChatRoom;
use App\Models\User;
use App\Repositories\EloquentUserQueries;
use App\Repositories\Interfaces\ChatRoomQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
Use Illuminate\Support\Facades\DB;


class ChatRoomController extends Controller
{
    public MessageController $message;
    private ChatRoomQueries $chatRoomQueries;
    private EloquentUserQueries $eloquentUserQueries;

    public function __construct()
    {
        $this->message = app(MessageController::class);
        $this->chatRoomQueries = app(ChatRoomQueries::class);
        $this->eloquentUserQueries = app(EloquentUserQueries::class);
    }

    /*
     * Get ChatRoom list by userId
     */
    public function chatRoomsByUser(int $id): JsonResponse
    {
        $chatRooms = $this->chatRoomQueries->getListByUserId($id);

        $chatRooms->each(function ($value) use ($id) {
            $value->title = $this->chatRoomQueries->getTitleByUserId($id, $value->id);
        });

        return response()->json([
            "status" => true,
            "chat_rooms" => $chatRooms
        ])->setStatusCode(200);
    }

    public function newChatRoomsByUser(int $chatRoomId, int $userId)
    {
        $chatRooms = $this->chatRoomQueries->getGtId($chatRoomId, $userId);

        return response()->json([
            "status" => true,
            "chat_rooms" => $chatRooms
        ])->setStatusCode(200);
    }

    public function show(int $chatRoomId, int $userId)
    {
        $chatRoom = $this->chatRoomQueries->getByChatId($chatRoomId, $userId);

        return response()->json([
            "status" => true,
            "chat_room" => $chatRoom
        ]);
    }

    /*
     * Create new chatRoom
     */
    public function store(StoreChatroomRequest $request)
    {
        $request_data = $request->validated();

        $chatRoom = null;
        $chatRoomId = $this->existChatRoomId($request_data);

        if (!$chatRoomId) {
            try {
                Db::transaction(function () use ($request_data, &$chatRoom) {
                    $chatRoom = new ChatRoom();
                    $chatRoom->title = $request_data['title'];

                    if(!$chatRoom->save()) {
                        throw new ChatNotCreated('Chat not created');
                    }

                    $chatRoom->users()->attach([
                            $request_data['sender_id'],
                            $request_data['receiver_id']
                        ]
                    );

                    $message_data = [
                        'sender_id' => $request_data['sender_id'],
                        'message' => "Чат создан!",
                        'audio' => null,
                        'chat_room_id' => $chatRoom->id
                    ];

                    $systemMessage = $this->message->store($message_data);
                    if (!$systemMessage) {
                        throw new ChatNotCreated('Can not send system message to new chat');
                    }

                }, 3);
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        }

        return response()->json([
            "status" => true,
            "chat_room_id" => $chatRoom->id ?? $chatRoomId
        ])->setStatusCode(201);

    }

    public function existChatRoomId($data)
    {
        $senderChatRooms = $this->eloquentUserQueries->chatroomByUser($data['sender_id']);
        $receiverChatRoms = $this->eloquentUserQueries->chatroomByUser($data['receiver_id']);

        foreach ($senderChatRooms as $key => $senderChat) {
            if ($receiverChatRoms->contains($senderChat)) {
                return $senderChat->chat_room;
            }
        }

        return false;
    }



}
