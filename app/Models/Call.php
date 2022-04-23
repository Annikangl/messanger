<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Call
 * @mixin Builder
 * @package App\Models
 * @property int $id;
 * @property int $sender_id;
 * @property int $receiver_id;
 * @property int $status;
 * @property string $duration;
 */

class Call extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id','receiver_id','status','duration'];

    public const STATUS_CALLING = 100;
    public const STATUS_ACCEPTED = 200;
    public const STATUS_SPEAKING = 201;

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
}
