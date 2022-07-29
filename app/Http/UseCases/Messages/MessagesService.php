<?php


namespace App\Http\UseCases\Messages;

use App\Exceptions\MessageException;
use App\Jobs\Message\SaveAudioJob;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessagesService
{
    public function create(array $message)
    {
        $chatRoom = $this->getChatRoom($message['chat_room_id']);


        if (!empty($message['audio']) && !is_null($message['audio'])) {
            return $this->createAudioMessage($message, $chatRoom);
        }

        return DB::transaction(function () use ($message, $chatRoom) {
            /** @var Message $message */
            $message = Message::make([
                'sender_id' => $message['sender_id'],
                'message' => $message['message'],
                'audio' => null,
            ]);

            $message->chatRoom()->associate($chatRoom);
            $message->save();

            $message->username = $this->getUser($message['sender_id'])->username;
            return $message;
        });
    }

    public function createAudioMessage(array $message, ChatRoom $chatRoom)
    {
        $audioMessagePath = 'user-' . $message['sender_id'] . '/voicemessages/voice_'
            . date('d:m:Y H:i:s') . '.arm';

        try {
            return DB::transaction(function () use ($message, $chatRoom, $audioMessagePath) {
                /** @var Message $audioMessage */
                $audioMessage = Message::make([
                    'sender_id' => $message['sender_id'],
                    'message' => null,
                    'audio' => $audioMessagePath,
                ]);

                $audioMessage->chatRoom()->associate($chatRoom);
                $audioMessage->save();

                $audioMessage->username = $this->getUser($message['sender_id'])->username;

                dispatch(new SaveAudioJob($message['audio'], $audioMessagePath));
                return $audioMessage;
            });
        } catch (MessageException) {
            throw new MessageException('Audio message not created');
        }
    }

    public function validate($message)
    {
        $validator = Validator::make($message, [
            'sender_id' => 'required|integer',
            'chat_room_id' => 'required|integer',
            'message' => Rule::requiredIf(empty($message['audio']) || is_null($message['audio'])),
            'audio' => Rule::requiredIf(empty($message['message']) || is_null($message['message'])),
        ]);

        if ($validator->fails()) {
            throw new MessageException('Message not validated');
        }
    }

    private function getChatRoom($id): ChatRoom
    {
        return ChatRoom::find($id);
    }

    private function getUser($id): User
    {
        return User::find($id);
    }

    private function getAudio($path): string
    {
        if (Storage::disk('local')->exists($path)) {
            return base64_encode(Storage::disk('local')->get($path));
        }
    }

}
