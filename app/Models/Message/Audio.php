<?php

namespace App\Models\Message;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Audio
 * @package App\Models\Message
 *
 * @property int $id
 * @property int $message_id
 * @property string $audio
 */
class Audio extends Model
{
    use HasFactory;

    protected $fillable = ['message_id', 'audio'];
    protected $table = 'message_audio';
}
