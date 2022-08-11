<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin Builder
 *
 * @property int id
 * @property int socket_id
 * @property string username
 * @property string email
 * @property string password
 * @property int active
 *
 * @method WithChatRooms($userId)
 */
class  User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'socket_id',
        'username',
        'email',
        'password',
        'avatar',
        'active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Chat rooms by user
     */
    public function chatRooms(): BelongsToMany
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_user')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class,'sender_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class, 'sender_id', 'id');
    }

    public function scopeWithChatRooms(Builder $query, $userId)
    {
        return $query->find($userId)->chatRooms->pluck('id');
    }
}
