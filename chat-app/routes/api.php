<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// AUTH (token-based)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {

    Route::get('chat-rooms', [ChatRoomController::class, 'index']);
    Route::post('chat-rooms', [ChatRoomController::class, 'store']);
    Route::delete('chat-rooms/{id}', [ChatRoomController::class, 'destroy']);

    Route::get('chat-rooms/{id}/messages', [MessageController::class, 'index']);
    Route::post('chat-rooms/{id}/messages', [MessageController::class, 'store']);
    Route::post('chat-rooms/{id}/read', [MessageController::class, 'markAsRead']);
});