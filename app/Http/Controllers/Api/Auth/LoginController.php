<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\UseCases\Auth\LoginService;

class LoginController extends Controller
{
    private LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = $this->loginService->login($request['email'], $request['password']);
            $token = $this->loginService->createToken($user, $request['email']);
        } catch (\DomainException $exception) {
            return response()->json(['status' => false,' error' => $exception->getMessage()]);
        }

        return response()->json([
            'status' => true,
            'user' => $user,
            'token' => $token
        ])->setStatusCode(200);
    }

}
