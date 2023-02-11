<?php

namespace App\Models;

use App\Exceptions\Calls\StatusException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Call
 * @mixin Builder
 * @package App\Models
 * @property int $id;
 * @property int $sender_id;
 * @property int $receiver_id;
 * @property int $status;
 * @property string $duration;
 *
 * @method Builder forUser(int $userId)
 * @method Builder greatThen(int $id, int $userId)
 */
class Call extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'receiver_id', 'status', 'duration'];

    public const STATUS_CALLING = 100;
    public const STATUS_ACCEPTED = 200;
    public const STATUS_SPEAKING = 201;

    public static array $errorStatuses = [400, 401, 402, 403];

    public function isCalling(): bool
    {
        return $this->status === self::STATUS_CALLING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isSpeaking(): bool
    {
        return $this->status === self::STATUS_SPEAKING;
    }

    public function changeStatus($status): void
    {
        $this->update([
            'status' => $status
        ]);
    }

    public function accept(int $status): void
    {
        if ($this->isAccepted()) {
            throw new StatusException('Call already accepted');
        }

        if ($status !== self::STATUS_ACCEPTED) {
            throw new StatusException('Ivalid status');
        }

        $this->update([
            'status' => self::STATUS_ACCEPTED
        ]);
    }

    public function close($status, $duration): void
    {
        $this->update([
            'status' => $status,
            'duration' => $duration
        ]);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('sender_id', $userId)->orWhere('receiver_id', $userId)
            ->with(['caller:id,username']);
    }

    public function scopeGreatThen(Builder $query, $id, $userId): Builder
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
        })->where(function ($query) use ($id) {
            $query->where('id', '>', $id);
        })->with(['caller:id,username']);
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
