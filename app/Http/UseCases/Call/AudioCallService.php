<?php


namespace App\Http\UseCases\Call;


use App\Models\Call;

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

    public function changeStatus($id, $status)
    {
        $call = Call::find($id);
        $call->status = $status;
        $call->save();

        return $call;
    }
}
