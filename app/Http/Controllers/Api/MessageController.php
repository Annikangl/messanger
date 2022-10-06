<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\UseCases\Messages\MessagesService;
use App\Models\User;
use App\Repositories\Interfaces\ChatRoomQueries;
use App\Repositories\Interfaces\MessageQueries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class MessageController extends Controller
{
    private MessageQueries $messageQueries;
    private ChatRoomQueries $chatRoomQueries;
    private MessagesService $service;

    public function __construct(MessagesService $service)
    {
        $this->messageQueries = app(MessageQueries::class);
        $this->chatRoomQueries = app(ChatRoomQueries::class);
        $this->service = $service;
    }

    /*
     * Get dialog by chat_room
     * $dialog - collect of messages by ChatRoomId
     * receiver_id -  receiver ID in chat room
     */
    public function index(int $chatRoomId, int $userId): JsonResponse
    {
        $dialog = $this->messageQueries->getPaginate($chatRoomId);

        foreach ($dialog as $key => $conversation) {
            if (!is_null($conversation->audio)) {
                $conversation->audio = base64_encode(Storage::disk('user_files')->get($conversation->audio));
            }
        }

        $receiver_id = $this->chatRoomQueries->getReceiverByChatRoom($chatRoomId, $userId);

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

    public function newOrAllMessages(int $chatRoomId, int $userId, int $messageId, $old = null): JsonResponse
    {
        $old ? $dialog = $this->messageQueries->getOldMessage($chatRoomId, $messageId)
            : $dialog = $this->messageQueries->getNewMessage($chatRoomId, $messageId);

        foreach ($dialog as $key => $conversation) {
            if (!is_null($conversation->audio)) {
                $conversation->audio = base64_encode(Storage::disk('user_files')->get($conversation->audio));
            }
        }

        $receiver_id = $this->chatRoomQueries->getReceiverByChatRoom($chatRoomId, $userId);

        return response()->json([
            "status" => true,
            "receiver_id" => $receiver_id,
            "dialog" => array_reverse($dialog->toArray()),
        ])->setStatusCode(200);
    }

    public function uploadFile(int $chatRoomId, int $userId, Request $request): JsonResponse
    {
        $path = User::getBaseFilePath($userId);

        $fileIds = [];

        if ($files = $request->allFiles()) {
            try {
                $this->validateFile($files);
                foreach ($files as $uploadedFile) {
                    /** @var UploadedFile $uploadedFile */
                    $file = $this->service->upload($uploadedFile, $path);
                    $fileIds[] = $file->id;
                }
            } catch (\DomainException | \Exception $exception) {
                return response()->json(['status' => false, 'error' => $exception->getMessage()])
                    ->setStatusCode(422);
            }
        }

        return response()->json(['status' => true, 'file_ids' => $fileIds])
            ->setStatusCode(200);
    }

    private function validateFile(array $files): void
    {
        foreach ($files as $name => $data) {
            $validator = \Validator::make($files, [
                $name => 'mimes:txt,doc,docx,xls,xlsx,pdf,jpg,jpeg,png,zip,7zip,rar|max:71680'
            ]);

            if ($validator->fails()) {
                throw new \DomainException($validator->errors()->first(), 422);
            }
        }
    }

}
