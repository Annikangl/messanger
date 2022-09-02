<?php


namespace App\Repositories\Interfaces;


interface UserQueries
{
    public function getAll($id);
    public function getById(int $id);
    public function getByUsername(string $username, int $userId);
    public function getUsernameById(int $id);
    public function getByEmail(string $email);
    public function chatroomByUser(int $id);
    public function getSocketId(int $id);
    public function getSocketIdByChatRoom(int $sender_id, int $receiver_id);
}
