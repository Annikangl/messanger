<?php

namespace App\Exceptions;

use Exception;

class MessageException extends Exception
{
    public function render()
    {
        return response()->json([
            "status" => false,
            "error" => $this->getMessage()
        ])->setStatusCode(500);
    }
}
