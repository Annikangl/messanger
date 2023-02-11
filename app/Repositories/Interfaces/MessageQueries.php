<?php


namespace App\Repositories\Interfaces;

interface MessageQueries
{
    public function getPaginate(int $chatRoomId,int $perPage = 15);
    public function getWithoutPaginate(int $chatRoomId);
    public function getNewMessage(int $chatRoomId, int $messageId);
    public function getOldMessage(int $chatRoomId, int $messageId);
    public function getTrashedMessages(int $chatRoomId);
}
