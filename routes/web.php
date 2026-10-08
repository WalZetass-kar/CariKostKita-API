<?php

use App\Http\Controllers\ControlCenterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — CariKostKita API Control Center
|--------------------------------------------------------------------------
|
| Interface developer internal untuk monitoring, dokumentasi, testing,
| dan telemetri request. Bukan landing page publik.
|
*/

Route::get('/', function (Request $request, ControlCenterController $controller) {
    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'name' => 'CariKostKita API',
            'version' => '1.0.0',
            'status' => 'online',
            'documentation' => url('/docs'),
            'health_check' => url('/api/health'),
            'endpoints' => url('/api'),
        ]);
    }

    return app()->call([$controller, 'overview']);
});

Route::get('/docs', [ControlCenterController::class, 'docs']);
Route::get('/docs/tester', [ControlCenterController::class, 'tester']);
Route::get('/docs/logs', [ControlCenterController::class, 'logs']);

// Retrofit Mobile Client GoTrue compatibility routes
Route::post('/auth/v1/token', [\App\Http\Controllers\Api\AuthController::class, 'handleTokenGrant']);
Route::post('/auth/v1/signup', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/auth/v1/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
Route::get('/auth/v1/user', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'getAuthUser']);
Route::put('/auth/v1/user', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'updateAuthUser']);
Route::post('/auth/v1/recover', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'recoverPassword']);

// Retrofit Mobile Client PostgREST Database compatibility routes
Route::get('/rest/v1/{table}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'select']);
Route::post('/rest/v1/{table}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'insert']);
Route::patch('/rest/v1/{table}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'update']);
Route::delete('/rest/v1/{table}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'delete']);
Route::post('/rest/v1/rpc/{fn}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'rpc']);

// Supabase Storage object proxy routes
Route::get('/storage/v1/object/public/{bucket}/{path}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'storageServePublic'])->where('path', '.*');
Route::post('/storage/v1/object/{bucket}/{path}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'storageUpload'])->where('path', '.*');
Route::post('/storage/v1/object/sign/{bucket}/{path}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'storageSign'])->where('path', '.*');
Route::delete('/storage/v1/object/{bucket}/{path}', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'storageDelete'])->where('path', '.*');

// Supabase Realtime Gateway
Route::any('/realtime/v1/websocket', [\App\Http\Controllers\Api\PostgrestGatewayController::class, 'realtimeInfo']);

Route::get('/auth/v1/authorize', function (Request $request) { return redirect(config('services.supabase.url') . '/auth/v1/authorize?' . $request->getQueryString()); });
