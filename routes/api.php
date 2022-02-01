<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChatRoomController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\Message;
use Illuminate\Http\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/{id}/chatroom', [ChatRoomController::class, 'chatRoomsByUser'])->name('chatRoomList');
    Route::post('/chatroom', [ChatRoomController::class, 'store'])->name('createChatRoom');

    Route::get('/user/{id}', [UserController::class, 'show'])
        ->name('getUserById');
    Route::get('/user/{id}/friends', [UserController::class, 'friends'])
        ->name('getUserFriends');
    Route::get('/user/{userId}/search/{username}', [UserController::class, 'searchUser'])
        ->where('search','.*');

    Route::get('/chat/{chatRoomId}/{userId}/dialog', [MessageController::class, 'index'])
        ->name('dialogWithPaginate');
    Route::get('/chat/{chatRoomId}/{userId}/{messageId}/{old?}', [MessageController::class, 'newOrAllMessages'])
        ->name('new-or-all-messages');

    Route::post('/chat/{id}', [MessageController::class, 'store'])->middleware(['cors']);
});


