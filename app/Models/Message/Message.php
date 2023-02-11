<?php

namespace App\Models\Message;

use App\Models\ChatRoom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class Message
 * @package App\Models\Message
 * @mixin Builder
 *
 * @property int $id
 * @property int $sender_id
 * @property int $receiver_id
 * @property string $type
 * @property ?string $message
 * @property ?string $username
 * @property array $file_ids
 * @property ?string $audio
 * @property int $chat_room_id
 * @property HasMany $files
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */

class Message extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_MESSAGE = 'message';
    const TYPE_AUDIO = 'audio';
    const TYPE_FILE = 'file';

    protected $fillable = ['sender_id', 'receiver_id', 'type', 'message', 'audio', 'chat_room_id'];

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
    public function audios(): HasMany
    {
        return $this->hasMany(Audio::class);
    }


    public function isAudio(): bool
    {
        return $this->type === self::TYPE_AUDIO;
    }


    public function isFile(): bool
    {
        return $this->type === self::TYPE_FILE;
    }

    /**
     * Convert created-at to format
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getAudioAttribute($value): ?string
    {
        if ($value) {
            return  base64_encode(Storage::disk('user_files')->get($value));
        }

        return null;
    }

    public function setMessageAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['message'] = 'Голосовое сообщение';
        } else {
            $this->attributes['message'] = $value;
        }
    }

    public function scopeLastInChat(Builder $query): Builder
    {
        return $query->select('message AS last_message', 'updated_at')->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(messages.id)'))->from('messages')->groupBy('messages.chat_room_id');
        })->orderBy('created_at', 'DESC');
    }

}
