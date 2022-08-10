<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Call;

class CallController extends Controller
{
    public function list(int $userId): JsonResponse
    {
        $calls = Call::forUser($userId)->latest()->get();

        return response()->json(['status' => true, 'calls' => $calls])
            ->setStatusCode(200);
    }

    public function listGtId($id, $userId): JsonResponse
    {
        $calls = Call::greatThen($id, $userId)->get();

        return response()->json(['status' => true, 'calls' => $calls])
            ->setStatusCode(200);
    }
}
