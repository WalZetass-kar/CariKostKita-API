<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Models\Chat;
use App\Models\Favorite;
use App\Models\Kost;
use App\Models\Room;
use App\Models\User;
use App\Services\ApiDocumentationService;
use App\Services\HealthCheckService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ControlCenterController extends Controller
{
    /**
     * Display API Control Center Overview.
     */
    public function overview(Request $request, HealthCheckService $healthService, ApiDocumentationService $docService): View
    {
        $health = $healthService->check();

        $stats = [
            'users' => User::count(),
            'kosts' => Kost::count(),
            'rooms' => Room::count(),
            'favorites' => Favorite::count(),
            'chats' => Chat::count(),
        ];

        $groupedRoutes = $docService->getGroupedRoutes();
        $totalEndpoints = array_reduce($groupedRoutes, fn ($carry, $group) => $carry + count($group), 0);

        $system = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'server_time' => now()->format('Y-m-d H:i:s T'),
            'app_url' => config('app.url'),
        ];

        return view('control-center.overview', compact('health', 'stats', 'groupedRoutes', 'totalEndpoints', 'system'));
    }

    /**
     * Display API Documentation.
     */
    public function docs(ApiDocumentationService $docService): View
    {
        $groupedRoutes = $docService->getGroupedRoutes();
        $totalEndpoints = array_reduce($groupedRoutes, fn ($carry, $group) => $carry + count($group), 0);

        return view('control-center.docs', compact('groupedRoutes', 'totalEndpoints'));
    }

    /**
     * Display REST API Tester.
     */
    public function tester(ApiDocumentationService $docService): View
    {
        $groupedRoutes = $docService->getGroupedRoutes();

        return view('control-center.tester', compact('groupedRoutes'));
    }

    /**
     * Display Request Monitoring Logs.
     */
    public function logs(): View
    {
        $logs = ApiLog::latest()->paginate(25);
        $totalLogged = ApiLog::count();
        $avgDuration = round((float) ApiLog::avg('duration_ms'), 2);

        return view('control-center.logs', compact('logs', 'totalLogged', 'avgDuration'));
    }
}
