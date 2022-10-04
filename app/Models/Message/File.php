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
 * @property string $extension
 * @property int $size
 */

class File extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['message_id', 'file', 'extension', 'size'];
    protected $table = 'message_files';

    public function calculateMegabytes($precision = 2): string
    {
        $base = log($this->size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .''. $suffixes[floor($base)];
    }


}
