<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Message extends Model
{
    /**
     * @mixin Builder
     */
    use HasFactory;

    protected $fillable = ['sender_id', 'message','audio','chat_room_id'];

    /*
     * Convert created_at to hours:minutes format
     */
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('H:i');
    }
}
