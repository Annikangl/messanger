<?php


namespace App\Classes;


use Illuminate\Http\UploadedFile;

class FileUploader
{
    public function upload(string $path, UploadedFile $file): void
    {
        if (!\Storage::disk('user_files')->putFileAs($path, $file, $file->getClientOriginalName())) {
            throw new \Exception('File ' . $file->getClientOriginalName() . ' not uploaded on server!');
        }
    }

    public function download(string $path)
    {

    }

}
