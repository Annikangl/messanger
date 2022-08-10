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

    public function close($id, $status, $duration = null)
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

    public function checkExist(int $user_id)
    {
//        $res_1 = Call::where('status', Call::STATUS_ACCEPTED)->where(function ($query) use ($user_id) {
//            $query->where('sender_id', $user_id)->orWhere('receiver_id', $user_id);
//        })->first();
//
//        $res_2 = Call::query()->where(function ($query) use ($user_id) {
//            $query->where('sender_id', $user_id)->orWhere('receiver_id', $user_id);
//        })->where(function ($query) {
//            $query->where('status', 200);
//        })->first();
//
//        dump($res_1->toSql(), $res_2->toSql());

        return Call::where('status', Call::STATUS_ACCEPTED)->where(function ($query) use ($user_id) {
            $query->where('sender_id', $user_id)->orWhere('receiver_id', $user_id);
        })->first();
    }
}
