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

    public function close($id, $status, $duration)
    {
        $call = $this->getCall($id);
        $call->close($status, $duration);
    }

    public function accept($id, $status)
    {
        $call = $this->getCall($id);
        $call->accept($status);
        return $call;
    }

    private function getCall($id)
    {
        return Call::find($id);
    }
}
