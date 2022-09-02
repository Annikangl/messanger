<?php


namespace App\Http\UseCases\User;

use App\Models\Call;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    public function setSocketId($userId, $socketId)
    {
        $user = $this->getUser($userId);

        $user->update([
            'socket_id' => $socketId
        ]);
    }

    public function getCallStatus(int $userId)
    {
        return Call::forUser($userId)->value('status');
    }

    private function getUser($userId): User|Builder
    {
        return User::query()->find($userId);
    }
}
