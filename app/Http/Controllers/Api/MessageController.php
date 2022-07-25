<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\MessageException;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Repositories\Interfaces\ChatRoomQueries;
use App\Repositories\Interfaces\MessageQueries;
use App\Repositories\Interfaces\UserQueries;
use http\Client\Request;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class MessageController extends Controller
{
    private MessageQueries $messageQueries;
    private ChatRoomQueries $chatRoomQueries;

    public function __construct()
    {
        $this->messageQueries = app(MessageQueries::class);
        $this->chatRoomQueries = app(ChatRoomQueries::class);
    }

    /*
     * Get dialog by chat_room
     * $dialog - collect of messages by ChatRoomId
     * receiver_id -  receiver ID in chat room
     */
    /**
     * @throws FileNotFoundException
     */
    public function index(int $chatRoomId, int $userId): JsonResponse
    {
        $dialog = $this->messageQueries->getWithPaginate($chatRoomId, 15);

        foreach ($dialog as $key => $conversation) {
            if (!is_null($conversation->audio)) {
                $conversation->audio = base64_encode(Storage::disk('local')->get($conversation->audio));
            }
        }

        $receiver_id = $this->chatRoomQueries->getReceiverByChatRoom($chatRoomId, $userId);

        return response()->json([
            "status" => true,
            "receiver_id" => $receiver_id,
            "dialog" => array_reverse($dialog->toArray()['data']),
            "pagination" =>  [
                'currentPage' => $dialog->currentPage(),
                'next_page_url' => $dialog->nextPageUrl(),
                'last_page_url' => $dialog->previousPageUrl()
            ]
        ])->setStatusCode(200);
    }

    public function newOrAllMessages(int $chatRoomId, int $userId,int $messageId, $old = null): JsonResponse
    {
        $old ? $dialog = $this->messageQueries->getOldMessage($chatRoomId, $messageId)
            : $dialog = $this->messageQueries->getNewMessage($chatRoomId, $messageId);

        foreach ($dialog as $key => $conversation) {
            if (!is_null($conversation->audio)) {
                $conversation->audio = base64_encode(Storage::disk('local')->get($conversation->audio));
            }
        }

        $receiver_id = $this->chatRoomQueries->getReceiverByChatRoom($chatRoomId, $userId);

        return response()->json([
            "status" => true,
            "receiver_id" => $receiver_id,
            "dialog" => array_reverse($dialog->toArray()),
        ])->setStatusCode(200);
    }

    /*
      * Create new message in chatRoom
     */

    public function store($data)
    {
        return $data['audio'] ? $this->storeAudioMessage($data) : $this->storeTextMessage($data);
    }

    public function storeTextMessage($message)
    {
        $validator = Validator::make($message, [
            'sender_id' => 'required|integer',
            'chat_room_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return \response()->json([
                "status" => false,
                "errors" => $validator->errors()->all()
            ]);
        }

        $userQueries = app(UserQueries::class);
        $newMessage = null;

        try {
            Db::transaction(function () use ($message, &$newMessage, $userQueries) {
                $newMessage = Message::create($message);

                $senderName = $userQueries->getUsernameById($newMessage->sender_id);
                $newMessage->username = $senderName;
            });
        } catch (MessageException $exception) {
            throw new MessageException('Message not created');
        }


        return $newMessage;
    }

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
        $path = 'user-' . $message['sender_id'];
        $audioMessagePath = 'user-' . $message['sender_id'] . '/voicemessages/voice_' . date('d:m:Y H:i:s') . '.arm';

        if (!Storage::disk('local')->exists($path)) {
            (new AuthController())->makeUserFolder($path);
        }

        Storage::disk('local')->put($audioMessagePath, $audioMessage);

        $userQueries = app(UserQueries::class);
        $newMessage = null;

        try {
            Db::transaction(function () use ($message, $userQueries, &$newMessage, $audioMessagePath) {
                $newMessage = Message::create([
                    'sender_id' => $message['sender_id'],
                    'message' => null,
                    'audio' => $audioMessagePath,
                    'chat_room_id' => $message['chat_room_id']
                ]);

                $senderName = $userQueries->getUsernameById($newMessage->sender_id);
                $newMessage->username = $senderName;
            });
        } catch (MessageException $exception) {
            throw new MessageException('Message not created');
        }

        $responseAudio = Storage::disk('local')->get($newMessage->audio);
        $newMessage->audio = base64_encode($responseAudio);

        return $newMessage;
    }
}
