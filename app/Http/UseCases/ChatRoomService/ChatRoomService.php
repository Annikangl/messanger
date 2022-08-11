<?php


namespace App\Http\UseCases\ChatRoomService;


use App\Exceptions\MessageException;
use App\Http\UseCases\Messages\MessagesService;
use App\Models\ChatRoom;
use App\Models\User;

class ChatRoomService
{
    public function create($title, $sender_id, $receiver_id): int
    {
        if (!$chatRoomId = $this->exists($sender_id, $receiver_id)) {
            return \DB::transaction(function () use ($title, $sender_id, $receiver_id) {
                /** @var ChatRoom $chatRoom */
                $chatRoom = ChatRoom::query()->create(['title' => $title]);

                $chatRoom->users()->attach([$sender_id, $receiver_id]);

                $message = [
                    'sender_id' => $sender_id,
                    'message' => "Чат создан!",
                    'audio' => null,
                    'chat_room_id' => $chatRoom->id
                ];

                /** @var MessagesService $messageService */
                $messageService = app(MessagesService::class);

                if (!$messageService->create($message)) {
                    throw new MessageException('Message not created in new chat');
                }

                return $chatRoom->id;
            });
        }

        return $chatRoomId;
    }

    private function exists(int $sender_id, int $receiver_id): int|bool
    {
        $senderChatRooms = $this->getChatRoomByUser($sender_id);
        $receiverChatRooms = $this->getChatRoomByUser($receiver_id);

        foreach ($senderChatRooms as $chatRoomId) {
            if ($receiverChatRooms->contains($chatRoomId)) return $chatRoomId;
        }

        return false;
    }

    private function getChatRoomByUser($userId)
    {
        return User::withChatRooms($userId);
    }
}
