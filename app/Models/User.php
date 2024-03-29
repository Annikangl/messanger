<?php

namespace App\Models;

use App\Models\Message\File;
use App\Models\Message\Message;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

    const STATUS_ONLINE = 'Р’ СЃРµС‚Рё';
    const STATUS_OFFLINE = 'РќРµ РІ СЃРµС‚Рё';

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

    public static function getBaseFilePath(int $userId): string
    {
        return 'user-' . $userId . '/files/';
    }

    public static function getBaseAudioPath(int $userId): string
    {
        return 'user-' . $userId . '/audiomessages/';
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

    public function files(): HasManyThrough
    {
        return $this->hasManyThrough(File::class, Message::class, 'sender_id');
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
