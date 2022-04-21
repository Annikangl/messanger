<?php


namespace App\Repositories\Interfaces;


interface ChatRoomQueries
{
    public function getListByUserId(int $id);
    public function getByChatId(int $chatRoomId, int $userId);
    public function getGtId(int $chatRoomId, int $userId);
    public function getByUserId(int $ud);
    public function getTitleByUserId(int $userId, int $chatRoomId);
    public function getReceiverByChatRoom($chatRoomId, $userId);
}
