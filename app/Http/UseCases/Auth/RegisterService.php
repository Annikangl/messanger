<?php


namespace App\Http\UseCases\Auth;

use App\Jobs\User\MakeFolderJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterService
{

    public function register(string $username, string $email, string $password, $status)
    {
        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'active' => $status
        ]);

        dispatch(new MakeFolderJob($user, 'user-' . $user->id));

        return $user;
    }

}
