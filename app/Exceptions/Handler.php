<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Nette\Schema\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

//    public function convertExceptionToResponse(Throwable $e, $request)
//    {
//       if ($e instanceof ValidationException && $request->expectsJson()) {
//           return response()->json([
//               "status" => false,
//               "messages" => ['You have errors'],
//               "errors"  => $e->errors()
//           ], 422);
//       }
//
//       parent::convertExceptionToResponse($e, $request);
//    }
}
