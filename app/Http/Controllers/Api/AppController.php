<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvertisementResource;
use App\Http\Resources\ApplicationReleaseResource;
use App\Models\AdAnalytic;
use App\Models\Advertisement;
use App\Models\AppEvent;
use App\Models\ApplicationRelease;
use App\Models\CrashReport;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppController extends Controller
{
    /**
     * Get latest application release info and APK download link (Landing Page & Mobile auto-update).
     */
    public function latestRelease(): JsonResponse
    {
        $release = ApplicationRelease::getLatest();

        if (! $release) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada rilis aplikasi yang tersedia.',
            ], 404);
        }

        return $this->successResponse(
            new ApplicationReleaseResource($release),
            'Informasi rilis aplikasi terbaru berhasil diambil'
        );
    }

    /**
     * Get platform settings and contact numbers (WhatsApp help, owner contact, github repo).
     */
    public function settings(): JsonResponse
    {
        $settings = PlatformSetting::all()->pluck('value', 'key')->all();

        // Default fallbacks matching Landing Page and Mobile App
        $data = [
            'app_name' => $settings['app_name'] ?? 'CariKostKita',
            'contact_whatsapp' => $settings['contact_whatsapp'] ?? '6281270008899',
            'owner_registration_whatsapp' => $settings['owner_registration_whatsapp'] ?? '6281270008899',
            'github_repo_url' => $settings['github_repo_url'] ?? 'https://github.com/g-423/KosKita',
            'default_city' => $settings['default_city'] ?? 'Pekanbaru',
            'coverage_areas' => [
                'Tampan', 'Sukajadi', 'Bukit Raya', 'Marpoyan Damai',
                'Payung Sekaki', 'Rumbai', 'Tenayan Raya', 'Lima Puluh',
                'Senapelan', 'Pekanbaru Kota', 'Rumbai Barat', 'Rumbai Timur',
                'Tuah Madani', 'Binawidya', 'Kulim',
            ],
            'campus_hubs' => [
                'Universitas Riau (UNRI)',
                'UIN Sultan Syarif Kasim (UIN Suska)',
                'Universitas Islam Riau (UIR)',
                'Universitas Lancang Kuning (Unilak)',
                'Politeknik Caltex Riau (PCR)',
            ],
            'maintenance_mode' => (bool) ($settings['platform_maintenance_mode'] ?? false),
        ];

        return $this->successResponse($data, 'Pengaturan platform berhasil diambil');
    }

    /**
     * Get active advertisements and banners for mobile app.
     */
    public function activeAds(): JsonResponse
    {
        $ads = Advertisement::active()->latest()->get();

        // Track impressions
        foreach ($ads as $ad) {
            $ad->increment('impressions');
            AdAnalytic::create([
                'advertisement_id' => $ad->id,
                'event_type' => 'impression',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth('sanctum')->id(),
            ]);
        }

        return $this->successResponse(
            AdvertisementResource::collection($ads),
            'Daftar iklan aktif berhasil diambil'
        );
    }

    /**
     * Track advertisement click.
     */
    public function recordAdClick($id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);
        $ad->increment('clicks');

        AdAnalytic::create([
            'advertisement_id' => $ad->id,
            'event_type' => 'click',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth('sanctum')->id(),
        ]);

        return $this->successResponse([
            'ad_id' => $ad->id,
            'target_url' => $ad->target_url,
            'total_clicks' => $ad->clicks,
        ], 'Klik iklan berhasil dicatat');
    }

    /**
     * Record Android crash report.
     */
    public function recordCrash(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_model' => ['nullable', 'string', 'max:255'],
            'os_version' => ['nullable', 'string', 'max:100'],
            'app_version' => ['nullable', 'string', 'max:100'],
            'exception_type' => ['nullable', 'string', 'max:255'],
            'stack_trace' => ['required', 'string'],
            'logs' => ['nullable', 'string'],
        ]);

        $crash = CrashReport::create([
            'user_id' => auth('sanctum')->id(),
            'device_model' => $validated['device_model'] ?? null,
            'os_version' => $validated['os_version'] ?? null,
            'app_version' => $validated['app_version'] ?? null,
            'exception_type' => $validated['exception_type'] ?? null,
            'stack_trace' => $validated['stack_trace'],
            'logs' => $validated['logs'] ?? null,
            'status' => 'unresolved',
        ]);

        return $this->successResponse(['id' => $crash->id], 'Laporan crash berhasil diterima');
    }

    /**
     * Record client telemetry event (search, view_detail, chat_start, survey).
     */
    public function recordEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:100'],
            'event_data' => ['nullable', 'array'],
        ]);

        $event = AppEvent::create([
            'user_id' => auth('sanctum')->id(),
            'event_name' => $validated['event_name'],
            'event_data' => $validated['event_data'] ?? null,
            'created_at' => now(),
        ]);

        return $this->successResponse(['id' => $event->id], 'Event analitik berhasil dicatat');
    }
}
