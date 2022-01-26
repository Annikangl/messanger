<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'message', 'audio', 'chat_room_id'];

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

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

}
