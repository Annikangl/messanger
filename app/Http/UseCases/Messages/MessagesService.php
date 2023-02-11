<?php


namespace App\Http\UseCases\Messages;

use App\Classes\FileUploader;
use App\Exceptions\MessageException;
use App\Jobs\Message\SaveAudioJob;
use App\Models\ChatRoom;
use App\Models\Message\Audio;
use App\Models\Message\File;
use App\Models\Message\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessagesService
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function create(array $message)
    {
        try {
            $this->validate($message);
        } catch (MessageException $exception) {
            throw new MessageException($exception->getMessage());
        }

        if (!empty($message['audio_id'])) {
            return $this->createAudioMessage($message);
        }

        if (isset($message['message']) && empty($message['file_ids'])) {
            return $this->createTextMessage($message);
        }

        if (!empty($message['file_ids'])) {
            return $this->createAttachmentMessage($message);
        }
    }

    private function createTextMessage(array $data): Message
    {
        $chatRoom = $this->getChatRoom($data['chat_room_id']);

        return DB::transaction(function () use ($data, $chatRoom) {
            /** @var Message $message */
            $message = Message::make([
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'type' => Message::TYPE_MESSAGE,
                'message' => $data['message'],
                'audio' => null,
            ]);

            $message->chatRoom()->associate($chatRoom);
            $message->save();

            $message->sender_username = $this->getUser($data['sender_id'])->username;
            $message->receiver_username = $this->getUser($data['receiver_id'])->username;
            return $message;
        });
    }

    private function createAudioMessage(array $message)
    {
        $audioMessagePath = 'user-' . $message['sender_id'] . '/voicemessages/voice_'
            . date('d:m:Y H:i:s') . '.arm';

        $chatRoom = $this->getChatRoom($message['chat_room_id']);

        try {
            return DB::transaction(function () use ($message, $chatRoom, $audioMessagePath) {
                /** @var Message $audioMessage */
                $audioMessage = Message::make([
                    'sender_id' => $message['sender_id'],
                    'receiver_id' => $message['receiver_id'],
                    'type' => Message::TYPE_AUDIO,
                    'message' => 'Голосове сообщение',
                    'audio' => null,
                ]);

                $audioMessage->chatRoom()->associate($chatRoom);
                $audioMessage->save();

                Audio::query()->where('id', $message['audio_id'])->update([
                    'message_id' => $audioMessage->id
                ]);

                dump($audioMessage->audios[0]->id);
                $audioMessage->username = $this->getUser($message['sender_id'])->username;
                $audioMessage->audio_id = $audioMessage->audios[0]->id;

//                dispatch(new SaveAudioJob($message['audio'], $audioMessagePath));
                return $audioMessage;
            });
        } catch (MessageException) {
            throw new MessageException('Audio message not created');
        }
    }

    private function createAttachmentMessage(array $data): Message
    {
        $chatRoom = $this->getChatRoom($data['chat_room_id']);

        try {
            return DB::transaction(function () use ($data, $chatRoom) {
                /** @var Message $message */
                $fileMessage = Message::make([
                    'sender_id' => $data['sender_id'],
                    'receiver_id' => $data['receiver_id'],
                    'type' => Message::TYPE_FILE,
                    'message' => $data['message'],
                    'audio' => null,
                ]);

                $fileMessage->chatRoom()->associate($chatRoom);
                $fileMessage->save();

                File::query()->whereIn('id', $data['file_ids'])->update([
                    'message_id' => $fileMessage->id
                ]);

                $file_info = collect();

                $fileMessage->files()->each(function ($value) use (&$file_info, $fileMessage) {
                    /** @var File $value */
                    $value->text_size = $value->calculateMegabytes();
                    $file_info->push($value);
                });

                $fileMessage->username = $this->getUser($data['sender_id'])->username;
                $fileMessage->file_ids = $file_info->toArray();
                return $fileMessage;
            });
        } catch (MessageException $exception) {
            throw new MessageException($exception->getMessage());
        }
    }

    public function removeForAll(int $message_id): void
    {
        $message = $this->getMessage($message_id);
        if (!$message) {
            throw new \DomainException('Message already deleted');
        }
        $message->delete();
    }

    public function upload(UploadedFile $file, string $path, $audio = null): File|Audio|Builder
    {
        $this->fileUploader->upload($path, $file);
        $filePath = $path . $file->getClientOriginalName();

        if ($audio) {
            return Audio::query()->create([
                'audio' => $filePath
            ]);
        }

        return File::query()->create([
            'filename' => $filePath,
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize()
        ]);
    }

    private function validate($message)
    {
        $validator = Validator::make($message, [
            'sender_id' => 'required|integer|exists:users,id',
            'receiver_id' => 'required|integer|exists:users,id',
            'chat_room_id' => 'required|integer|exists:chat_rooms,id',
            'type' => 'required|string',
//            'message' => Rule::requiredIf(empty($message['audio']) || is_null($message['audio'])),
//            'audio' => Rule::requiredIf(empty($message['message']) || is_null($message['message'])),
        ]);

        if ($validator->fails()) {
            throw new MessageException('Message not validated ' . $validator->errors()->first());
        }
    }

    private function getChatRoom($id): ChatRoom
    {
        return ChatRoom::find($id);
    }

    private function getUser($id): Builder|User
    {
        return User::query()->find($id);
    }

    private function getMessage($id): Builder|Message
    {
        return Message::query()->find($id);
    }

    private function getAudio($path): string
    {
        if (Storage::disk('local')->exists($path)) {
            return base64_encode(Storage::disk('local')->get($path));
        }
    }

}
