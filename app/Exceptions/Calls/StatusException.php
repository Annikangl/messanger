<?php

namespace App\Exceptions\Calls;

use Exception;

class StatusException extends Exception
{
    public function render()
    {
        return response()->json(["status" => false, "error" => $this->getMessage()])
            ->setStatusCode(404);
    }
}
