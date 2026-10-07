<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class ApiHealthController extends Controller
{
    public function __invoke(HealthCheckService $healthService): JsonResponse
    {
        $health = $healthService->check();

        $isOk = $health['overall_status'] === 'ok';
        $statusCode = $isOk ? 200 : 503;

        return response()->json([
            'success' => $isOk,
            'status' => $health['overall_status'],
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'api' => $health['api']['status'],
                'database' => $health['database']['status'],
                'storage' => $health['storage']['status'],
                'cache' => $health['cache']['status'],
            ],
            'details' => [
                'database' => [
                    'driver' => $health['database']['driver'],
                    'latency_ms' => $health['database']['latency_ms'],
                ],
                'storage' => [
                    'provider' => $health['storage']['provider'],
                    'bucket' => $health['storage']['bucket'],
                ],
            ],
        ], $statusCode);
    }
}
