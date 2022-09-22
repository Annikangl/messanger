<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChatRoomController;
use App\Http\Controllers\Api\MessageController;


Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::group(['prefix' => 'user','as' => 'user.'], function () {
        Route::get('/{id}/all', [UserController::class, 'index'])->name('index');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/friends', [UserController::class, 'friends'])->name('friends');
        Route::get('/{id}/search/{username}', [UserController::class, 'searchUser'])->where('username','.*');
    });

    Route::group(['prefix' => 'chatroom', 'as' => 'chatroom.'], function () {
        Route::get('/user/{id}', [ChatRoomController::class, 'listByUser'])->name('listByUser');
        Route::get('/{chatRoomId}/user/{userId}/new', [ChatRoomController::class, 'listByUserGtId'])->name('byUserGtId');
        Route::post('/', [ChatRoomController::class, 'store'])->name('store');
        Route::delete('/{chatRoomId}', [ChatRoomController::class, 'destroy'])->name('remove');
    });

    Route::group(['prefix' => 'call', 'as' => 'call.'], function () {
        Route::get('/user/{userId}', [CallController::class, 'list']);
        Route::get('/{id}/user/{userId}/gt', [CallController::class, 'listGtId'])->name('gt-id');
    });

    Route::group(['prefix' => 'message', 'as' => 'message.'], function () {
        Route::post('upload/chatroom/{chatRoomId}/user/{userId}', [MessageController::class, 'uploadFile'])->name('upload');
        Route::get('/chatroom/{chatRoomId}/user/{userId}/dialog/trashed', [MessageController::class, 'trashedList']);
    });



    Route::get('/chat/{chatRoomId}/{userId}/dialog', [MessageController::class, 'index'])
        ->name('dialogWithPaginate');
    Route::get('/chat/{chatRoomId}/{userId}/{messageId}/{old?}', [MessageController::class, 'newOrAllMessages'])
        ->name('new-or-all-messages');

    Route::post('/chat/{id}', [MessageController::class, 'store'])->middleware(['cors']);
});


