<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Call;
use Illuminate\Support\Facades\Validator;

class CallController extends Controller
{
    public function store(array $request)
    {
        $validator = Validator::make($request, [
            'sender_id' => 'required|integer|min:1',
            'receiver_id' => 'required|integer|min:1',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors()->all()
            ],503);
        }

        return Call::create([
            "sender_id" => $request['sender_id'],
            "receiver_id" => $request['receiver_id'],
            "status" => $request['status'],
        ]);
    }

    public function update(array $request)
    {
//        $request = new UpdateCallRequest($request);
//        $validated = $request->validated();

        $call = Call::find($request['call_id']);
        $call->status = $request['status'];
        $call->save();

        return $call;
    }
}
