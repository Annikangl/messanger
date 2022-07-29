<?php


namespace App\Repositories\Interfaces;


interface CallQueries
{
    public function getAll();
    public function getById(int $id);
    public function getByUser($id);
}
