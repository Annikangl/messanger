<?php


namespace App\Repositories\Interfaces;


use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserQueries
{
    public function getAll($id);
    public function getById(int $id);
    public function getByUsername(string $username, int $userId);
    public function getUsernameById(int $id);
    public function getByEmail(string $email);
    public function chatroomByUser(int $id);
}
