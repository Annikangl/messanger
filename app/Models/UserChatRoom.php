<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChatRoom extends Model
{
    use HasFactory;

    protected $table = 'chatroom_user';
    protected $fillable = ['user_id','chatroom_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }


}
