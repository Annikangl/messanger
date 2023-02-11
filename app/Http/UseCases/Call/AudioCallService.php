<?php


namespace App\Http\UseCases\Call;

use App\Models\Call;
use Illuminate\Database\Eloquent\Builder;

class AudioCallService
{
    public function create(array $request): Call
    {
        $result = Call::create([
            'sender_id' => $request['sender_id'],
            'receiver_id' => $request['receiver_id'],
            'status' => $request['status']
        ]);

        return $result;
    }

    public function close($id, $status, $duration = null): void
    {
        $call = $this->getCall($id);
        $call->close($status, $duration);
    }

    public function accept($id, $status): Call
    {
        $call = $this->getCall($id);
        $call->accept($status);
        return $call;
    }

    private function getCall($id): Call|Builder
    {
        return Call::query()->find($id);
    }

    public function checkExist(int $user_id): ?Call
    {
        return Call::where('status', Call::STATUS_ACCEPTED)->where(function ($query) use ($user_id) {
            $query->where('sender_id', $user_id)->orWhere('receiver_id', $user_id);
        })->first();
    }
}
