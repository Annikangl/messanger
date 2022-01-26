<?php


namespace App\Repositories\Interfaces;


interface ChatRoomQueries
{
    public function getListByUserId(int $id);
    public function getByUserId(int $ud);
    public function getTitleByUserId(int $userId, int $chatRoomId);
    public function getReceiverByChatRoom($chatRoomId, $userId);
}
