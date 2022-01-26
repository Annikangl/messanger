<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ChatNotCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatroomRequest;
use App\Repositories\Interfaces\ChatRoomQueries;
use Illuminate\Http\JsonResponse;
Use Illuminate\Support\Facades\DB;


class ChatRoomController extends Controller
{
    public UserChatRoomController $usersChatRoom;
    public MessageController $message;
    private ChatRoomQueries $chatRoomQueries;

    public function __construct()
    {
        $this->usersChatRoom = app(UserChatRoomController::class);
        $this->message = app(MessageController::class);
        $this->chatRoomQueries = app(ChatRoomQueries::class);
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
            "chats" => $chatRooms
        ])->setStatusCode(200);
    }

    /*
     * Create new chatRoom
     */
    /**
     * @throws ChatNotCreated
     */
    public function store(StoreChatroomRequest $request)
    {
        $request_data = $request->validated();

        $chatRoomId = $this->existChatRoomId($request_data);

        if (!$chatRoomId) {
            try {
                Db::transaction(function () use ($request_data, &$chatRoomId) {
                    $chatRoomId = Db::table('chat_rooms')->insertGetId(
                        ['title' => $request_data['title']]
                    );

                    // create record into users_chat_rooms table
                    $this->usersChatRoom->store([
                        'sender_id' => $request_data['sender_id'],
                        'receiver_id' => $request_data['receiver_id'],
                        'chat_room_id' => $chatRoomId
                    ]);

                    $message_data = [
                        'sender_id' => $request_data['sender_id'],
                        'message' => "Чат создан!",
                        'audio' => null,
                        'chat_room_id' => $chatRoomId
                    ];

                    $this->message->store($message_data);

                }, 3);
            } catch (ChatNotCreated $exception) {
                return new ChatNotCreated('Chat can not created');
            }
        }

        return response()->json([
            "status" => true,
            "chat_room_id" => $chatRoomId
        ])->setStatusCode(201);

    }

    public function existChatRoomId($data)
    {
        $senderChatRooms = DB::table('users_chat_rooms')
                            ->select('chat_room_id AS chat_room')
                            ->where('user_id', [$data['sender_id']])
                            ->get();

        $receiverChatRoms = DB::table('users_chat_rooms')
            ->select('chat_room_id AS chat_room')
            ->where('user_id', [$data['receiver_id']])
            ->get();

        foreach ($senderChatRooms as $key => $senderChat) {
            if ($receiverChatRoms->contains($senderChat)) {
                return $senderChat->chat_room;
            }
        }
        return false;
    }



}
