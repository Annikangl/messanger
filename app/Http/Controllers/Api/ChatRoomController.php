<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ChatNotCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatroomRequest;
use App\Http\UseCases\Messages\MessagesService;
use App\Models\ChatRoom;
use App\Models\Message;
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
    private $messageSerive;

    public function __construct(MessagesService $messagesService)
    {
        $this->message = app(MessageController::class);
        $this->chatRoomQueries = app(ChatRoomQueries::class);
        $this->eloquentUserQueries = app(EloquentUserQueries::class);
        $this->messageSerive = $messagesService;
    }

    /*
     * Get ChatRoom list by userId
     */
    public function chatRoomsByUser(int $id): JsonResponse
    {
        $chatRooms = $this->chatRoomQueries->getListByUser($id);

//        $chatRooms->each(function ($value) use ($id) {
//            $value->title = $this->chatRoomQueries->getTitleByUserId($id, $value->id);
//        });

        return response()->json(["status" => true, "chat_rooms" => $chatRooms])
            ->setStatusCode(200);
    }

    public function newChatRoomsByUser(int $chatRoomId, int $userId)
    {
        $chatRooms = $this->chatRoomQueries->getGtId($chatRoomId, $userId);

        $chatRooms->each(function ($value) use ($userId) {
            $value->title = $this->chatRoomQueries->getTitleByUserId($userId, $value->id);
        });

        return response()->json(["status" => true, "chat_rooms" => $chatRooms])
            ->setStatusCode(200);
    }

//    public function show(int $chatRoomId, int $userId)
//    {
//        $chatRoom = $this->chatRoomQueries->getByChatId($chatRoomId, $userId);
//
//        return response()->json([
//            "status" => true,
//            "chat_room" => $chatRoom
//        ]);
//    }

    /*
     * Create new chatRoom
     */
    public function store(StoreChatroomRequest $request)
    {
        $chatRoom = null;
        $chatRoomId = $this->existChatRoomId($request);

        if (!$chatRoomId) {
            try {
                Db::transaction(function () use ($request, &$chatRoom) {
                    $chatRoom = new ChatRoom();
                    $chatRoom->title = $request['title'];

                    if(!$chatRoom->save()) {
                        throw new ChatNotCreated('Chat not created');
                    }

                    $chatRoom->users()->attach([
                            $request['sender_id'],
                            $request['receiver_id']
                        ]
                    );

                    $message_data = [
                        'sender_id' => $request['sender_id'],
                        'message' => "Чат создан!",
                        'audio' => null,
                        'chat_room_id' => $chatRoom->id
                    ];

                    $systemMessage = $this->messageSerive->create($message_data);
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
                return $senderChat->chat_room_id;
            }
        }

        return false;
    }

    private function getChatRoom($id)
    {
        return ChatRoom::query()->find($id);
    }



}
