<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
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
 * @method static Builder|User query()
 * @method Builder|Collection withChatRooms($userId)
 */
class  User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const STATUS_ONLINE = 'В сети';
    const STATUS_OFFLINE = 'Не в сети';

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
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function setOnline(): static
    {
        $this->active = self::STATUS_ONLINE;
        $this->save();
        return $this;
    }

    public function setOffline(): static
    {
        $this->active = self::STATUS_ONLINE;
        $this->save();
        return $this;
    }

    public function chatRooms(): BelongsToMany
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_user')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class,'sender_id');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'sender_id', 'id');
    }


    /**
     * @param Builder $query
     * @param $userId
     * @return Collection
     */
    public function scopeWithChatRooms(Builder $query, $userId): Collection
    {
        return $query->find($userId)->chatRooms->pluck('id');
    }

    /**
     * Convert created_at to valid time
     * @param $value
     * @return string|null
     */
    public function getCreatedAtAttribute($value): ?string
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone(\Config::get('app.timezone'))
            ->toDateTimeString();
    }

    /**
     * Convert updated_at to valid time
     * @param $value
     * @return string|null
     */
    public function getUpdatedAtAttribute($value): ?string
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone(\Config::get('app.timezone'))
            ->toDateTimeString();
    }
}
