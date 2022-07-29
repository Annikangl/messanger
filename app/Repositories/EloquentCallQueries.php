<?php


namespace App\Repositories;


use App\Models\Call;
use App\Repositories\Interfaces\CallQueries;

class EloquentCallQueries implements CallQueries
{

    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    public function getById(int $id)
    {
        // TODO: Implement getById() method.
    }

    public function getByUser($id)
    {
        return Call::forUser($id)->latest()->get();
    }
}
