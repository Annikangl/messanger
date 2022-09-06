<?php


namespace App\Repositories;

use App\Models\Call;
use App\Repositories\Interfaces\CallQueries;
use Illuminate\Database\Eloquent\Collection;

class EloquentCallQueries implements CallQueries
{
    public function getByUser($id): Collection|array
    {
        $key = __CLASS__ . '_user_' . $id . '_calls';

        $result = \Cache::tags('calls')->remember($key, 60*10, function () use ($id) {
            return Call::forUser($id)->latest()->get();
        });

        return $result;
    }

    public function getByUserGreatThen(int $userId, int $callId)
    {
        $key = __CLASS__ . '_user_' . $userId . '_calls_GT';

        $result = \Cache::tags('calls')->remember($key, 60*10, function () use ($userId, $callId) {
            return Call::greatThen($callId, $userId)->get();
        });

        return $result;
    }
}
