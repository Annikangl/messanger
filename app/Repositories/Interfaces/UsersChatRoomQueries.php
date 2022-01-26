<?php


namespace App\Repositories\Interfaces;


interface UsersChatRoomQueries
{
    public function getReceiverByChatRoom(int $chatRoomId, int $userId);
}
