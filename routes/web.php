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
