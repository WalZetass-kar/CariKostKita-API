<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\KostController;
use App\Http\Controllers\Api\KostImageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\ApiHealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/health', ApiHealthController::class);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/kosts', [KostController::class, 'index']);
Route::get('/kosts/{id}', [KostController::class, 'show']);
Route::get('/kosts/{kost}/rooms', [RoomController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user/profile', [ProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | Favorites
    |--------------------------------------------------------------------------
    */
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{kost}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{kost}', [FavoriteController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Chats & Messages
    |--------------------------------------------------------------------------
    */
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{chat}/messages', [ChatController::class, 'messages']);
    Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage']);

    /*
    |--------------------------------------------------------------------------
    | Owner & Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:pemilik,admin')->prefix('owner')->group(function () {
        Route::get('/kosts', [KostController::class, 'myKosts']);
        Route::post('/kosts', [KostController::class, 'store']);
        Route::put('/kosts/{kost}', [KostController::class, 'update']);
        Route::delete('/kosts/{kost}', [KostController::class, 'destroy']);

        Route::post('/kosts/{kost}/rooms', [RoomController::class, 'store']);
        Route::put('/rooms/{room}', [RoomController::class, 'update']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);

        Route::post('/kosts/{kost}/images', [KostImageController::class, 'store']);
        Route::delete('/images/{image}', [KostImageController::class, 'destroy']);
        Route::put('/images/{image}/primary', [KostImageController::class, 'setPrimary']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/kosts', [AdminController::class, 'kosts']);
        Route::get('/kosts/pending', [AdminController::class, 'pendingKosts']);
        Route::put('/kosts/{id}/approve', [AdminController::class, 'approveKost']);
        Route::put('/kosts/{id}/reject', [AdminController::class, 'rejectKost']);
        Route::delete('/kosts/{id}', [AdminController::class, 'destroyKost']);
        Route::get('/reports', [AdminController::class, 'reports']);
        Route::put('/reports/{report}/status', [AdminController::class, 'updateReportStatus']);
    });
});
