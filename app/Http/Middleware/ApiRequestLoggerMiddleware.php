<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestLoggerMiddleware
{
    protected float $startTime;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->startTime = microtime(true);

        return $next($request);
    }

    /**
     * Handle tasks after response has been sent to browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $duration = round((microtime(true) - ($this->startTime ?? microtime(true))) * 1000, 2);

            // Record only API routes
            if ($request->is('api/*')) {
                ApiLog::create([
                    'method' => $request->method(),
                    'path' => '/'.ltrim($request->path(), '/'),
                    'status' => $response->getStatusCode(),
                    'duration_ms' => $duration,
                    'ip_address' => $request->ip(),
                ]);
            }
        } catch (\Throwable $e) {
            // Silently ignore logging failures to prevent disrupting API consumers
        }
    }
}
