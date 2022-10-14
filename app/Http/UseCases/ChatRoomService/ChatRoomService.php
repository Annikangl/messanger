<?php


namespace App\Http\UseCases\ChatRoomService;

use App\Exceptions\ChatRoom\ChatRoomException;
use App\Http\UseCases\Messages\MessagesService;
use App\Models\ChatRoom;
use App\Models\Message\Message;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Class ChatRoomService
 * @package App\Http\UseCases\ChatRoomService
 */
class ChatRoomService
{
    /**
     * Create new chatroom
     * @param $title
     * @param $sender_id
     * @param $receiver_id
     * @return int
     * @throws \Throwable
     */
    public function create($title, $sender_id, $receiver_id): int
    {
        if (!$chatRoomId = $this->exists($sender_id, $receiver_id)) {
            return \DB::transaction(function () use ($title, $sender_id, $receiver_id) {
                /** @var ChatRoom $chatRoom */
                $chatRoom = ChatRoom::query()->create(['title' => $title]);

                $chatRoom->users()->attach([$sender_id, $receiver_id]);

                $message = [
                    'type' => Message::TYPE_MESSAGE,
                    'sender_id' => $sender_id,
                    'receiver_id' => $receiver_id,
                    'message' => "Чат создан!",
                    'audio' => null,
                    'chat_room_id' => $chatRoom->id
                ];

                /** @var MessagesService $messageService */
                $messageService = app(MessagesService::class);

                if (!$messageService->create($message)) {
                    throw new ChatRoomException('Message not sent in the chat');
                }

                return $chatRoom->id;
            });
        }

        return $chatRoomId;
    }

    /**
     * Check exist chatroom between users
     * @param int $sender_id
     * @param int $receiver_id
     * @return int|bool
     */
    private function exists(int $sender_id, int $receiver_id): int|bool
    {
        $senderChatRooms = $this->getChatRoomByUser($sender_id);
        $receiverChatRooms = $this->getChatRoomByUser($receiver_id);

        foreach ($senderChatRooms as $chatRoomId) {
            if ($receiverChatRooms->contains($chatRoomId)) return $chatRoomId;
        }

        return false;
    }


    /**
     * Return chatrooms ids by user id
     * @param $userId
     * @return Collection
     */
    private function getChatRoomByUser($userId): Collection
    {
        return User::query()->withChatRooms($userId);
    }
}
