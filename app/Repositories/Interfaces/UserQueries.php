<?php


namespace App\Repositories\Interfaces;


use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserQueries
{
    public function getById(int $id): User;
    public function getByUsername(string $username, int $userId): Collection;
}
