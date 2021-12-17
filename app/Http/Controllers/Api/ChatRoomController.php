<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ChatNotCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatroomRequest;
use App\Models\ChatRoom;
use App\Models\UserChatRoom;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
Use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatRoomController extends Controller
{
    public $usersChatRoom;
    public $message;

    public function __construct()
    {
        $this->usersChatRoom = new UserChatRoomController();
        $this->message = new MessageController();
    }

    /*
     * Get ChatRoom list by userId
     */
    public function chatRoomsByUser(int $id)
    {
        $chatRooms = DB::table('users_chat_rooms')
                        ->join('chat_rooms', 'users_chat_rooms.chat_room_id', 'chat_rooms.id')
                        ->join('users', 'users_chat_rooms.user_id', 'users.id')
                        ->join('messages', 'messages.chat_room_id', 'chat_rooms.id')
                        ->select('chat_rooms.id', 'chat_rooms.title','messages.message AS last_message')
                        ->whereNotIn('users.username',[1,3])
                        ->where('users.id', $id)
                        ->whereIn('messages.id', function ($query) {
                            $query->select(DB::raw('MAX(messages.id)'))->from('messages')->groupBy('messages.chat_room_id');
                        })
                        ->orderBy('messages.created_at', 'desc')
                        ->get();

        $chatRooms->each(function ($value) use ($id){
            $value->title = Db::table('users')
                                ->join('users_chat_rooms','users.id','users_chat_rooms.user_id')
                                ->select('users.username')
                                ->where('users_chat_rooms.chat_room_id', $value->id)
                                ->where('users_chat_rooms.user_id','<>', $id)->value('username');
        });

        $chatRooms->last(function ($value) {
            if (is_null($value->last_message)) {
                return $value->last_message = 'Голосовое сообщение';
            }
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
