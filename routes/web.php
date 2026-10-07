<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'name' => 'CariKostKita API',
            'version' => '1.0.0',
            'status' => 'online',
            'endpoints' => url('/api'),
        ]);
    }

    return view('welcome');
});
