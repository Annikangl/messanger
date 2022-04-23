<?php


namespace App\Http\UseCases\User;


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

    private function getUser($id): User
    {
        return User::find($id);
    }
}
