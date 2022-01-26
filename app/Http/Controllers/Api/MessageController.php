<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\MessageException;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Repositories\Interfaces\ChatRoomQueries;
use App\Repositories\Interfaces\MessageQueries;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
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
    public function index(int $chatRoomId, int $userId, $all = null): \Illuminate\Http\JsonResponse
    {
        $all ? $dialog = $this->messageQueries->getWithoutPaginate($chatRoomId)
            : $dialog = $this->messageQueries->getWithPaginate($chatRoomId, 15);

        foreach ($dialog as $key => $conversation) {
            if (!is_null($conversation->audio)) {
                $conversation->audio = base64_encode(Storage::disk('local')->get($conversation->audio));
            }
        }

        $receiver_id = $this->chatRoomQueries->getReceiverByChatRoom($chatRoomId, $userId);

        return response()->json([
            "status" => true,
            "receiver_id" => $receiver_id,
            "dialog" => $all ? array_reverse($dialog->toArray()) : array_reverse($dialog->toArray()['data']),
            "pagination" => $all ? [] : [
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
     * @throws FileNotFoundException
     * @throws MessageException
     */
    public function store($data)
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
                $newMessage = Message::create($message);

                $senderName = DB::table('users')->select('username')->where('id', [$newMessage->sender_id])->first();
                $newMessage->username = $senderName->username;
            });
        } catch (MessageException $exception) {
            throw new MessageException('Message not created');
        }


        return $newMessage;
    }

    /**
     * @throws FileNotFoundException
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
        $path = 'user-' . $message['sender_id'];
        $audioMessagePath = 'user-' . $message['sender_id'] . '/voicemessages/voice_' . date('d:m:Y H:i:s') . '.arm';

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

                $senderName = DB::table('users')->select('username')->where('id', [$newMessage->sender_id])->first();
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
