<?php


namespace App\Http\UseCases\User;

use App\Models\Call;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

    public function setOnline(int $userId): User
    {
        $user = $this->getUser($userId);
        return $user->setOnline();
    }

    public function setOffline(): int
    {
        return User::query()->update(['active' => User::STATUS_OFFLINE]);
    }

    private function getUser($userId): User|Builder
    {
        return User::query()->find($userId);
    }

    private function getUserBySocketId(int $socketId): \Illuminate\Database\Eloquent\Model|Builder|null
    {
        return User::query()->where('socket_id', $socketId)->first();
    }




}
