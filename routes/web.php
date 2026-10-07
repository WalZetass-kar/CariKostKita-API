<?php

use App\Models\Kost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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

    $kosts = Schema::hasTable('kosts')
        ? Kost::with(['owner', 'primaryImage', 'rooms'])->approved()->latest()->get()
        : collect();

    return view('welcome', compact('kosts'));
});
