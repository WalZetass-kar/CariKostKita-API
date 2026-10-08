<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\KostController;
use App\Http\Controllers\Api\KostImageController;
use App\Http\Controllers\Api\PostgrestGatewayController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\SafetyController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\ApiHealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — CariKostKita Single API Gateway
|--------------------------------------------------------------------------
|
| Serves Mobile App (Android), Landing Page, and Admin Dashboard (Admin-Gue).
| Supports both root /api/... and versioned /api/v1/... endpoints.
|
*/

$registerApiEndpoints = function () {
    /*
    |--------------------------------------------------------------------------
    | Public & Application Routes (Mobile & Landing Page)
    |--------------------------------------------------------------------------
    */
    Route::get('/health', ApiHealthController::class);
    Route::get('/app/latest-release', [AppController::class, 'latestRelease']);
    Route::get('/app/settings', [AppController::class, 'settings']);
    Route::get('/app/ads/active', [AppController::class, 'activeAds']);
    Route::post('/app/ads/{id}/click', [AppController::class, 'recordAdClick']);
    Route::post('/app/crashes', [AppController::class, 'recordCrash']);
    Route::post('/app/events', [AppController::class, 'recordEvent']);

    // Authentication (email, password & Google)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [AuthController::class, 'googleLogin']);
    Route::post('/auth/google-login', [AuthController::class, 'googleLogin']);
    Route::post('/google-login', [AuthController::class, 'googleLogin']);
    Route::post('/auth/v1/token', [AuthController::class, 'handleTokenGrant']);
    Route::post('/auth/v1/signup', [AuthController::class, 'register']);
    Route::post('/auth/v1/logout', [AuthController::class, 'logout']);
    Route::get('/auth/v1/user', [PostgrestGatewayController::class, 'getAuthUser']);
    Route::put('/auth/v1/user', [PostgrestGatewayController::class, 'updateAuthUser']);
    Route::post('/auth/v1/recover', [PostgrestGatewayController::class, 'recoverPassword']);

    // PostgREST Database compatibility endpoints
    Route::get('/rest/v1/{table}', [PostgrestGatewayController::class, 'select']);
    Route::post('/rest/v1/{table}', [PostgrestGatewayController::class, 'insert']);
    Route::patch('/rest/v1/{table}', [PostgrestGatewayController::class, 'update']);
    Route::delete('/rest/v1/{table}', [PostgrestGatewayController::class, 'delete']);
    Route::post('/rest/v1/rpc/{fn}', [PostgrestGatewayController::class, 'rpc']);

    // Supabase Storage & Realtime Gateway endpoints
    Route::get('/storage/v1/object/public/{bucket}/{path}', [PostgrestGatewayController::class, 'storageServePublic'])->where('path', '.*');
    Route::post('/storage/v1/object/{bucket}/{path}', [PostgrestGatewayController::class, 'storageUpload'])->where('path', '.*');
    Route::post('/storage/v1/object/sign/{bucket}/{path}', [PostgrestGatewayController::class, 'storageSign'])->where('path', '.*');
    Route::delete('/storage/v1/object/{bucket}/{path}', [PostgrestGatewayController::class, 'storageDelete'])->where('path', '.*');
    Route::any('/realtime/v1/websocket', [PostgrestGatewayController::class, 'realtimeInfo']);

    // Public Kosts Catalog
    Route::get('/kosts', [KostController::class, 'index']);
    Route::get('/kosts/map', [KostController::class, 'map']);
    Route::get('/kosts/{id}', [KostController::class, 'show']);
    Route::get('/kosts/{kost}/rooms', [RoomController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated User Routes (Pencari & Pemilik)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Profile & Account Management
        Route::get('/user', [ProfileController::class, 'show']);
        Route::get('/user/profile', [ProfileController::class, 'show']);
        Route::put('/user/profile', [ProfileController::class, 'update']);
        Route::put('/user/password', [ProfileController::class, 'updatePassword']);
        Route::delete('/user/account', [ProfileController::class, 'deleteAccount']);
        Route::post('/user/verification', [ProfileController::class, 'submitVerification']);

        // Favorites
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{kost}', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{kost}', [FavoriteController::class, 'destroy']);

        // Chats & Messages
        Route::get('/chats', [ChatController::class, 'index']);
        Route::post('/chats', [ChatController::class, 'store']);
        Route::get('/chats/{chat}/messages', [ChatController::class, 'messages']);
        Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage']);
        Route::patch('/chats/{chat}/read', [ChatController::class, 'markAsRead']);

        // Surveys & Verified Reviews
        Route::post('/surveys', [SurveyController::class, 'store']);
        Route::get('/surveys', [SurveyController::class, 'index']);
        Route::patch('/surveys/{id}/cancel', [SurveyController::class, 'cancel']);
        Route::post('/reviews', [SurveyController::class, 'storeReview']);

        // User Safety & Reports
        Route::post('/safety/block', [SafetyController::class, 'blockUser']);
        Route::post('/safety/report-user', [SafetyController::class, 'reportUser']);
        Route::post('/reports/kost', [SafetyController::class, 'reportKost']);
        Route::post('/reports', [SafetyController::class, 'reportKost']);

        // Notifications & Device Tokens
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/fcm-token', [\App\Http\Controllers\Api\NotificationController::class, 'updateFcmToken']);
        Route::post('/user/fcm-token', [\App\Http\Controllers\Api\NotificationController::class, 'updateFcmToken']);

        /*
        |--------------------------------------------------------------------------
        | Owner Routes (Pemilik Kost)
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:pemilik,admin')->prefix('owner')->group(function () {
            Route::get('/kosts', [KostController::class, 'myKosts']);
            Route::post('/kosts', [KostController::class, 'store']);
            Route::put('/kosts/{kost}', [KostController::class, 'update']);
            Route::delete('/kosts/{kost}', [KostController::class, 'destroy']);
            Route::post('/kosts/{kost}/resubmit', [KostController::class, 'resubmitKost']);

            Route::get('/analytics', [KostController::class, 'ownerAnalytics']);
            Route::get('/surveys', [SurveyController::class, 'ownerSurveys']);
            Route::patch('/surveys/{id}', [SurveyController::class, 'updateOwnerSurvey']);

            // Rooms & Media management
            Route::post('/kosts/{kost}/rooms', [RoomController::class, 'store']);
            Route::put('/rooms/{room}', [RoomController::class, 'update']);
            Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);

            Route::post('/kosts/{kost}/images', [KostImageController::class, 'store']);
            Route::delete('/images/{image}', [KostImageController::class, 'destroy']);
            Route::put('/images/{image}/primary', [KostImageController::class, 'setPrimary']);
        });

        /*
        |--------------------------------------------------------------------------
        | Admin Dashboard Routes (Admin-Gue)
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // BFF for Admin Dashboard to replace db.json
            Route::get('/bff/load-all', [App\Http\Controllers\Api\AdminBffController::class, 'loadAll']);

            // Dashboard Analytics & Charts
            Route::get('/dashboard', [AdminController::class, 'dashboard']);

            // User & Owner Management
            Route::get('/users', [AdminController::class, 'users']);
            Route::patch('/users/{id}/status', [AdminController::class, 'toggleUserStatus']);
            Route::get('/owners', [AdminController::class, 'owners']);
            Route::patch('/owners/{id}/status', [AdminController::class, 'toggleUserStatus']);

            // Kost Moderation & CRUD
            Route::get('/kosts', [AdminController::class, 'kosts']);
            Route::get('/kosts/pending', [AdminController::class, 'pendingKosts']);
            Route::get('/kosts/{id}', [AdminController::class, 'showKost']);
            Route::post('/kosts', [AdminController::class, 'storeKost']);
            Route::put('/kosts/{id}', [AdminController::class, 'updateKost']);
            Route::patch('/kosts/{id}/status', [AdminController::class, 'updateKostStatus']);
            Route::put('/kosts/{id}/approve', [AdminController::class, 'approveKost']);
            Route::put('/kosts/{id}/reject', [AdminController::class, 'rejectKost']);
            Route::delete('/kosts/{id}', [AdminController::class, 'destroyKost']);

            // Reports Kanban & Moderation
            Route::get('/reports', [AdminController::class, 'reports']);
            Route::put('/reports/{report}/status', [AdminController::class, 'updateReportStatus']);
            Route::get('/reports/users', [AdminController::class, 'userReports']);
            Route::patch('/reports/users/{id}', [AdminController::class, 'updateUserReportStatus']);

            // Advertisement Management
            Route::get('/ads', [AdminController::class, 'ads']);
            Route::post('/ads', [AdminController::class, 'storeAd']);
            Route::put('/ads/{id}', [AdminController::class, 'updateAd']);
            Route::delete('/ads/{id}', [AdminController::class, 'deleteAd']);
            Route::patch('/ads/{id}/status', [AdminController::class, 'toggleAdStatus']);
            Route::get('/ads/analytics', [AdminController::class, 'adAnalytics']);

            // Audit Logs
            Route::get('/audit-logs', [AdminController::class, 'auditLogs']);

            // Application Releases
            Route::get('/releases', [AdminController::class, 'releases']);
            Route::post('/releases', [AdminController::class, 'storeRelease']);

            // Platform Settings
            Route::get('/settings', [AdminController::class, 'settings']);
            Route::put('/settings', [AdminController::class, 'updateSettings']);

            // Broadcast / User Notifications
            Route::post('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'store']);
        });
    });
};

// Register routes directly at root /api/...
$registerApiEndpoints();

// Register routes also at versioned /api/v1/...
Route::prefix('v1')->group($registerApiEndpoints);
