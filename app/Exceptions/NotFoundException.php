<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public function report()
    {
        //
    }

    public function render()
    {
        return response()->json(["status" => false, "error" => $this->getMessage()])
            ->setStatusCode(404);
    }
}
