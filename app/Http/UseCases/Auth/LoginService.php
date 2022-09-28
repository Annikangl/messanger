<?php


namespace App\Http\UseCases\Auth;

use App\Models\User;
use App\Repositories\Interfaces\UserQueries;
use Illuminate\Validation\ValidationException;

class LoginService
{
    private UserQueries $userRepository;

    public function __construct(UserQueries $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(string $email, string $password): User
    {
        /** @var User $user */
        $user = $this->userRepository->getByEmail($email);

        if (!$user || ! \Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'error' => ['The provided credentials are incorrect'],
            ]);
        }

        return $user;
    }

    public function createToken(User $user, string $tokenString): string
    {
        return $user->createToken($tokenString)->plainTextToken;
    }
}
