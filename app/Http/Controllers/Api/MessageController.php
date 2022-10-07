<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\UseCases\Messages\MessagesService;
use App\Models\Message\File;
use App\Models\User;
use App\Repositories\Interfaces\ChatRoomQueries;
use App\Repositories\Interfaces\MessageQueries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;


class MessageController extends Controller
{
    private MessageQueries $messageQueries;
    private MessagesService $service;

    public function __construct(MessagesService $service)
    {
        $this->messageQueries = app(MessageQueries::class);
        $this->service = $service;
    }

    /**
     * Get dialog by chat_room
     * $dialog - collect of messages by ChatRoomId
     * receiver_id -  receiver ID in chat room
     */
    public function index(int $chatRoomId, int $userId): JsonResponse
    {
        /** @var Paginator $dialog */
        $dialog = $this->messageQueries->getPaginate($chatRoomId);

        $this->putdAudioInfo($dialog);
        $this->putFileInfo($dialog);

        return response()->json([
            "status" => true,
            "receiver_id" => $dialog->isNotEmpty() ? $dialog->first()['receiver_id'] : null,
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

        return response()->json([
            'status' => true,
            'receiver_id' => $dialog->isNotEmpty() ? $dialog->first()['receiver_id'] : null,
            'dialog' => array_reverse($dialog->toArray()),
        ])->setStatusCode(200);
    }

    // TODO Split to two methods
    public function newOrAllMessages(int $chatRoomId, int $userId, int $messageId, $old = null): JsonResponse
    {
        $old ? $dialog = $this->messageQueries->getOldMessage($chatRoomId, $messageId)
            : $dialog = $this->messageQueries->getNewMessage($chatRoomId, $messageId);

        $this->putdAudioInfo($dialog);
        $this->putFileInfo($dialog);

        return response()->json([
            "status" => true,
            "receiver_id" => $dialog->isNotEmpty() ? $dialog->first()['receiver_id'] : null,
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

    private function putFileInfo($dialog): void
    {
        foreach ($dialog as $key => $conversation) {
            foreach ($conversation['files'] as $file) {
                /** @var File $file */
                $file->text_size = $file->calculateMegabytes();
            }
        }
    }

    private function putdAudioInfo($dialog): void
    {
        foreach ($dialog as $key => $conversation) {
            if (!is_null($conversation['audio'])) {
                $conversation['audio'] = base64_encode(Storage::disk('user_files')->get($conversation['audio']));
            }
        }
    }

}
