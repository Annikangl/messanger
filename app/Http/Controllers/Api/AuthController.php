<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\UserNotFoundExceprion;
use App\Http\Requests\RegisterUserRequest;
use  App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $credentials = $request->validated();

        $userId = $this->creteUserAccount($credentials);

        return response()->json([
            "status" => true,
            "user" => $userId
        ]);
    }

    /**
     * @throws UserNotFoundExceprion
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator  = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "messages" => $validator->errors()
            ])->setStatusCode(422);
        }

        if (!Auth::attempt($credentials)) {
           throw new UserNotFoundExceprion('User not found');
        }

        $token = $request->user()->createToken('usertoken');

        return response()->json([
            "status" => true,
            "user" => Auth::user(),
            "token" => $token->plainTextToken
        ])->setStatusCode(200);

    }

    public function creteUserAccount($request)
    {
        $userId = null;

        DB::transaction(function () use ($request, &$userId) {
            $newUser = User::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'active' => $request['active']
            ]);

            if (!$newUser) {
                throw new \Exception('User not created');
            }

            $path = 'user-' . $newUser->id;
            $this->makeUserFolder($path);
            User::where('id',$newUser->id)->update(['folder' => $path]);
            $userId = $newUser->id;
        });

        return $userId;
    }

    public function makeUserFolder(string $path)
    {
        if (!Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->makeDirectory($path);
        }

        return new FileExistsException('Folder already exist');

    }
}
