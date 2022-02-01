<?php


namespace App\Repositories\Interfaces;



interface MessageQueries
{
    public function getWithPaginate(int $chatRoomId,int $perPage);
    public function getWithoutPaginate(int $chatRoomId);
    public function getNewMessage(int $chatRoomId, int $messageId);
    public function getOldMessage(int $chatRoomId, int $messageId);
}
