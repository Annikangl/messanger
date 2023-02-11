<?php


namespace App\Repositories\Interfaces;


interface ChatRoomQueries
{
    public function getListByUser(int $id);
    public function getListByUserGtId(int $chatRoomId, int $userId);
    public function getTitleByUserId(int $userId, int $chatRoomId);
    public function getReceiverByChatRoom($chatRoomId, $userId);
}
