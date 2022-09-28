<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ChatRoomQueries;
use App\Repositories\Interfaces\MessageQueries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


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

    public function trashedList(int $chatRoomId, int $userId): JsonResponse
    {
        $dialog = $this->messageQueries->getTrashedMessages($chatRoomId);
        $receiver_id = $this->chatRoomQueries->getReceiverByChatRoom($chatRoomId, $userId);

        return response()->json([
            'status' => true,
            'receiver_id' => $receiver_id,
            'dialog' => array_reverse($dialog->toArray()),
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

    public function uploadFile(int $chatRoomId, int $userId, Request $request)
    {

        Log::info('request', ['data' => $request->allFiles()]);

        foreach ($request->allFiles() as $file) {
            Log::info('File', ['file' => $file->getClientOriginalName()]);
        }

        return ['status' => true];

    }

}
