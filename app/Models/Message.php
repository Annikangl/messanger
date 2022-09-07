<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class Message
 * @package App\Models
 * @mixin Builder
 *
 * @property int $sender_id
 * @property int $receiver_id
 * @property ?string $message
 * @property ?string $audio
 * @property int $chat_room_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['sender_id', 'receiver_id', 'message', 'audio', 'chat_room_id'];

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    // TODO casts[]
    /*
     * Convert created_at to hours:minutes format
     */
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('H:i');
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
