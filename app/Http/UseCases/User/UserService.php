<?php


namespace App\Http\UseCases\User;


use App\Models\Call;
use App\Models\User;

class UserService
{
    public function setSocketId($userId, $socketId)
    {
        $user = $this->getUser($userId);

        $user->update([
            'socket_id' => $socketId
        ]);
    }

    public function getSocketId($userId)
    {
        return User::where('id', $userId)->value('socket_id');
    }

    public function getCallStatus(int $userId)
    {
        return Call::forUser($userId)->value('status');
    }
}
