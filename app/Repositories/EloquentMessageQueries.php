<?php


namespace App\Repositories;

use App\Models\ChatRoom;
use App\Models\Message\Message;
use App\Repositories\Interfaces\MessageQueries;
use Illuminate\Database\Eloquent\Collection;

class EloquentMessageQueries implements MessageQueries
{
    public function getPaginate(int $chatRoomId, $perPage = 15): \Illuminate\Contracts\Pagination\Paginator
    {
        $result = Message::query()->with('files')->where('chat_room_id', $chatRoomId)
            ->latest('messages.created_at')
            ->simplePaginate($perPage)
            ->through(function ($item) {
                /** @var Message $item */
                return [
                    'message_id' => $item->id,
                    'sender_id' => $item->sender_id,
                    'receiver_id' => $item->receiver_id,
                    'message' => $item->message,
                    'audio' => $item->audio,
                    'created_at' => $item->created_at,
                    'files' => $item->files
                ];
            });

        return $result;
    }

    public function getTrashedMessages($chatRoomId)
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->withTrashed()
            ->whereNotNull('deleted_at')
            ->select('messages.id as message_id', 'messages.sender_id as sender_id', 'messages.message')
            ->latest('messages.deleted_at')
            ->get();

        return $result;
    }

    public function getWithoutPaginate(int $chatRoomId): Collection
    {
        $result = ChatRoom::find($chatRoomId)
            ->messages()
            ->select('messages.sender_id as sender_id', 'messages.message', 'messages.audio', 'messages.created_at')
            ->latest('messages.created_at')
            ->get();

        return $result;
    }

    public function getNewMessage(int $chatRoomId, int $messageId): \Illuminate\Support\Collection
    {
        return Message::query()->with('files')
            ->where('chat_room_id', $chatRoomId)
            ->where('messages.id', '>', $messageId)->get()
            ->map(function ($item) {
                /** @var Message $item */
                return [
                    'message_id' => $item->id,
                    'sender_id' => $item->sender_id,
                    'receiver_id' => $item->receiver_id,
                    'message' => $item->message,
                    'audio' => $item->audio,
                    'created_at' => $item->created_at,
                    'files' => $item->files
                ];
            });
    }

    public function getOldMessage(int $chatRoomId, int $messageId): \Illuminate\Support\Collection
    {
        return Message::query()->with('files')->where('chat_room_id', $chatRoomId)
            ->where('messages.id', '<', $messageId)->get()
            ->map(function ($item) {
                /** @var Message $item */
                return [
                    'message_id' => $item->id,
                    'sender_id' => $item->sender_id,
                    'receiver_id' => $item->receiver_id,
                    'message' => $item->message,
                    'audio' => $item->audio,
                    'created_at' => $item->created_at,
                    'files' => $item->files
                ];
            });
    }


}
