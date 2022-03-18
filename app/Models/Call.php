<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Call
 * @package App\Models
 * @property int $id;
 * @property int $sender_id;
 * @property int $receiver_id;
 * @property int $status;
 * @property  string $duration;
 */

class Call extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id','receiver_id','status','duration'];
}
