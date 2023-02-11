<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\UseCases\Auth\RegisterService;
use App\Http\Requests\User\RegisterRequest;

class RegisterController extends Controller
{
    private RegisterService $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->registerService->register(
                $request['username'],
                $request['email'],
                $request['password'],
                $request['active']
            );
        } catch (\DomainException $exception) {
            return response()->json(['status' => false, 'error' => $exception->getMessage()]);
        }

        return response()->json(['status' => true, 'user' => $user])
            ->setStatusCode(201);
    }
}
