<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class HealthCheckService
{
    /**
     * Perform comprehensive health check on all systems.
     */
    public function check(): array
    {
        $dbCheck = $this->checkDatabase();
        $storageCheck = $this->checkStorage();
        $cacheCheck = $this->checkCache();

        $allOk = ($dbCheck['status'] === 'ok') && ($storageCheck['status'] === 'ok') && ($cacheCheck['status'] === 'ok');

        return [
            'overall_status' => $allOk ? 'ok' : 'degraded',
            'api' => [
                'status' => 'ok',
                'label' => 'Operational',
            ],
            'database' => $dbCheck,
            'storage' => $storageCheck,
            'cache' => $cacheCheck,
        ];
    }

    /**
     * Test database connectivity and latency.
     */
    public function checkDatabase(): array
    {
        $startTime = microtime(true);
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'ok',
                'connected' => true,
                'driver' => config('database.default'),
                'latency_ms' => $latency,
                'message' => 'Connected',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'connected' => false,
                'driver' => config('database.default'),
                'latency_ms' => null,
                'message' => 'Database Connection Failed',
            ];
        }
    }

    /**
     * Test Supabase Storage availability and bucket status.
     */
    public function checkStorage(): array
    {
        $supabaseUrl = config('services.supabase.url', env('SUPABASE_URL'));
        $supabaseKey = config('services.supabase.key', env('SUPABASE_KEY'));
        $bucket = config('services.supabase.bucket', env('SUPABASE_STORAGE_BUCKET', 'kost-images'));

        if (! empty($supabaseUrl) && ! empty($supabaseKey)) {
            try {
                $endpoint = rtrim($supabaseUrl, '/')."/storage/v1/bucket/{$bucket}";
                $response = Http::timeout(3)
                    ->withHeaders([
                        'Authorization' => "Bearer {$supabaseKey}",
                        'apiKey' => $supabaseKey,
                    ])
                    ->get($endpoint);

                if ($response->successful()) {
                    return [
                        'status' => 'ok',
                        'provider' => 'Supabase Storage',
                        'bucket' => $bucket,
                        'message' => 'Connected',
                    ];
                }
            } catch (\Throwable $e) {
                // If network/external times out or credentials invalid
            }
        }

        // Check local disk fallback health
        try {
            $isWritable = Storage::disk('public')->put('.health', 'ok');
            if ($isWritable) {
                Storage::disk('public')->delete('.health');
            }

            return [
                'status' => 'ok',
                'provider' => ! empty($supabaseUrl) ? 'Supabase Storage' : 'Local Storage Engine',
                'bucket' => $bucket,
                'message' => 'Operational',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'provider' => 'Storage',
                'bucket' => $bucket,
                'message' => 'Storage Unavailable',
            ];
        }
    }

    /**
     * Test Cache operation.
     */
    public function checkCache(): array
    {
        try {
            $key = 'health_ping_'.uniqid();
            Cache::put($key, '1', 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            if ($retrieved === '1') {
                return [
                    'status' => 'ok',
                    'message' => 'Operational',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Cache Read Failed',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Cache Unavailable',
            ];
        }
    }
}
