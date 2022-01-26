<?php


namespace App\Repositories\Interfaces;


interface MessageQueries
{
    public function getWithPaginate(int $chatRoomId,$perPage);
    public function getWithoutPaginate(int $chatRoomId);
    public function getUsernameById(int $userId);
}
