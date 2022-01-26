<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChatRoom extends Model
{
    use HasFactory;

    protected $table = 'users_chat_rooms';
    protected $fillable = ['user_id','chat_room_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }


}
