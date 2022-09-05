<?php


namespace App\Repositories;

use App\Models\Call;
use App\Repositories\Interfaces\CallQueries;
use Illuminate\Database\Eloquent\Collection;

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

    public function getByUser($id): Collection|array
    {
        return Call::forUser($id)->latest()->get();
    }
}
