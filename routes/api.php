<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChatRoomController;
use App\Http\Controllers\Api\MessageController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::group(['prefix' => 'user','as' => 'user.'], function () {
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/friends', [UserController::class, 'friends'])->name('friends');
        Route::get('/{userId}/search/{username}', [UserController::class, 'searchUser'])->where('search','.*');
    });

    Route::group(['prefix' => 'chatroom', 'as' => 'chatroom.'], function () {
        Route::get('/{chatRoomId}/user/{userId}', [ChatRoomController::class, 'show'])->name('show');
        Route::get('/user/{id}', [ChatRoomController::class, 'chatRoomsByUser'])->name('listByUser');
        Route::get('/{chatRoomId}/user/{userId}/new', [ChatRoomController::class, 'newChatRoomsByUser'])->name('newListByUser');
        Route::post('/', [ChatRoomController::class, 'store'])->name('store');
        Route::delete('/{chatRoomId}', [ChatRoomController::class, 'destroy'])->name('remove');
    });



    Route::get('/chat/{chatRoomId}/{userId}/dialog', [MessageController::class, 'index'])
        ->name('dialogWithPaginate');
    Route::get('/chat/{chatRoomId}/{userId}/{messageId}/{old?}', [MessageController::class, 'newOrAllMessages'])
        ->name('new-or-all-messages');

    Route::post('/chat/{id}', [MessageController::class, 'store'])->middleware(['cors']);
});


