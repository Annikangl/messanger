<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageNotificationEvent;
use App\Exceptions\MessageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;



class MessageController extends Controller
{
    /*
     * Get dialog by chat_room
     * $dialog - collect of messages by ChatRoomId
     * receiver_id -  receiver ID in chat room
     */
    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function index(int $chatRoomId, int $userId)
    {
        $dialog = DB::table('messages')
            ->join('chat_rooms','messages.chat_room_id','chat_rooms.id')
            ->select('messages.sender_id as sender_id', 'messages.message','messages.audio',
                    DB::raw("DATE_FORMAT(messages.created_at, '%h:%i') as created_at"))
            ->where('messages.chat_room_id', [$chatRoomId])
            ->orderBy('messages.created_at', 'DESC')
            ->simplePaginate(15);

        foreach ($dialog as $key => $conversation) {
            if (!is_null($conversation->audio)) {
                $conversation->audio = base64_encode(Storage::disk('local')->get($conversation->audio));
            }
        }

        $receiver_id = DB::table('users_chat_rooms')
            ->select('user_id as receiver_id')
            ->where('users_chat_rooms.chat_room_id',[$chatRoomId])
            ->where('users_chat_rooms.user_id', '<>',[$userId])
            ->value('receiver_id');


        return response()->json([
            "status" => true,
            "receiver_id" => $receiver_id,
            "dialog" => array_reverse($dialog->toArray()['data']),
            "pagination" => [
                'currentPage' => $dialog->currentPage(),
                'next_page_url' => $dialog->nextPageUrl(),
                'last_page_url' => $dialog->previousPageUrl()
            ]
        ])->setStatusCode(200);
    }

    /*
      * Create new message in chatRoom
     */
    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws MessageException
     */
    public function store($data): Message
    {
        return $data['audio'] ? $this->storeAudioMessage($data) : $this->storeTextMessage($data);
    }

    /**
     * @throws MessageException
     */
    public function storeTextMessage($message)
    {
        $validator = Validator::make($message, [
            'sender_id' => 'required|integer',
            'message' => 'required|string',
            'chat_room_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return \response()->json([
                "status" => false,
                "errors" => $validator->errors()->all()
            ]);
        }

        $newMessage = null;

        try {
            Db::transaction(function () use ($message, &$newMessage) {
                $newMessage = Message::create([
                    'sender_id' => $message['sender_id'],
                    'message' => $message['message'],
                    'chat_room_id' => $message['chat_room_id']
                ]);

                $senderName = DB::table('users')->select('username')->where('id',[$newMessage->sender_id])->first();
                $newMessage->username = $senderName->username;
            });
        } catch (MessageException $exception) {
            throw new MessageException('Message not created');
        }

        return $newMessage;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws MessageException
     */
    public function storeAudioMessage($message)
    {
        $validator = Validator::make($message, [
            'sender_id' => 'required|integer',
            'audio' => 'required',
            'chat_room_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return \response()->json([
                "status" => false,
                "errors" => $validator->errors()->all()
            ]);
        }

        $audioMessage = base64_decode($message['audio']);
        $path = 'user-'. $message['sender_id'];
        $audioMessagePath =  'user-'. $message['sender_id'] . '/voicemessages/voice_' . date('d:m:Y H:i:s') . '.arm';

        if (!Storage::disk('local')->exists($path)) {
            (new AuthController())->makeUserFolder($path);
        }

        Storage::disk('local')->put($audioMessagePath, $audioMessage);

        $newMessage = null;

        try {
            Db::transaction(function () use ($message, &$newMessage, $audioMessagePath) {
                $newMessage = Message::create([
                    'sender_id' => $message['sender_id'],
                    'message' => null,
                    'audio' => $audioMessagePath,
                    'chat_room_id' => $message['chat_room_id']
                ]);

                $senderName = DB::table('users')->select('username')->where('id',[$newMessage->sender_id])->first();
                $newMessage->username = $senderName->username;
            });
        } catch (MessageException $exception) {
            throw new MessageException('Message not created');
        }

        $responseAudio = Storage::disk('local')->get($newMessage->audio);
        $newMessage->audio = base64_encode($responseAudio);

        return $newMessage;
    }
}
