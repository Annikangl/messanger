<?php


namespace App\Http\UseCases\Messages;


use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessagesService
{
    public function create(array $request)
    {
        $chatRoom = ChatRoom::find($request['chat_room_id']);

        return DB::transaction(function () use ($request, $chatRoom) {

            $message = Message::make([
                'sender_id' => $request['sender_id'],
                'message' => $request['message'],
                'audio' => $request['audio'],
            ]);

            $message->chatRoom()->associate($chatRoom);
            $message->save();

            $username = User::find($message->sender_id)->value('username');

            // TODO map username to collection
            return $message;
        });

    }
}
