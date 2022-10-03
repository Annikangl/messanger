<?php

namespace App\Models\Message;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class File
 * @package App\Models\Message
 * @property int $id
 * @property int $message_id
 * @property string $file
 */

class File extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['message_id', 'file'];
    protected $table = 'message_files';


}
