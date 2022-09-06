<?php


namespace App\Repositories\Interfaces;


interface CallQueries
{
    public function getByUser(int $id);
    public function getByUserGreatThen(int $userId, int $callId);
}
