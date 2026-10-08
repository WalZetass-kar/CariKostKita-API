<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Kost;
use App\Models\Report;
use App\Models\Advertisement;
use App\Models\ApplicationRelease;
use App\Models\PlatformSetting;

class AdminBffController extends Controller
{
    /**
     * Aggregates all data to replace db.json for the Admin Dashboard.
     */
    public function loadAll(Request $request): JsonResponse
    {
        $admin = $request->user();

        // 1. Fetch Users & Owners
        $allUsers = User::all();
        $owners = $allUsers->whereIn('role', ['pemilik', 'owner', 'pemilik_kost'])->values();
        $users = $allUsers->whereIn('role', ['pencari', 'user'])->values();
        
        // 2. Fetch Kosts
        $kosts = Kost::all();

        // 3. Fetch Reports
        $reports = Report::all();

        // 4. Fetch Ads
        $ads = Advertisement::all();

        // 5. Fetch Releases
        $releases = ApplicationRelease::all();

        // 6. Fetch System Settings
        $settings = PlatformSetting::all()->pluck('value', 'key')->toArray();

        // 7. Format the mega-object exactly as Admin-Gue expects
        $data = [
            'success' => true,
            'data' => [
                'owners' => $owners->map(fn($o) => [
                    'id' => $o->id,
                    'name' => $o->name ?? $o->display_name ?? '',
                    'email' => $o->email,
                    'phone' => $o->phone ?? '',
                    'role' => $o->role,
                    'verificationStatus' => $o->verification_status ?? 'Approved',
                    'archived' => false,
                    'createdAt' => $o->created_at->format('Y-m-d'),
                ]),
                'users' => $users->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name ?? $u->display_name ?? '',
                    'email' => $u->email,
                    'phone' => $u->phone ?? '',
                    'role' => $u->role,
                    'verificationStatus' => $u->verification_status ?? 'Approved',
                    'archived' => false,
                    'createdAt' => $u->created_at->format('Y-m-d'),
                ]),
                'kosts' => $kosts->map(fn($k) => [
                    'id' => $k->id,
                    'name' => $k->name,
                    'ownerId' => $k->owner_id,
                    'area' => $k->district ?? 'Pekanbaru',
                    'address' => $k->address ?? '',
                    'price' => $k->price ?? 0,
                    'status' => $k->status ?? 'Aktif',
                    'verificationStatus' => $k->verification_status ?? 'Approved',
                    'archived' => false,
                    'createdAt' => $k->created_at ? $k->created_at->format('Y-m-d') : date('Y-m-d'),
                ]),
                'reports' => $reports->map(fn($r) => [
                    'id' => $r->id,
                    'reporterId' => $r->reporter_id,
                    'targetId' => $r->target_id,
                    'type' => $r->type,
                    'reason' => $r->reason,
                    'status' => $r->status,
                    'createdAt' => $r->created_at ? $r->created_at->format('Y-m-d') : date('Y-m-d'),
                ]),
                'ads' => $ads->map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'type' => $a->type,
                    'targetUrl' => $a->target_url,
                    'isActive' => $a->is_active,
                    'impressions' => $a->impressions ?? 0,
                    'clicks' => $a->clicks ?? 0,
                    'createdAt' => $a->created_at ? $a->created_at->format('Y-m-d') : date('Y-m-d'),
                ]),
                'releases' => $releases->map(fn($r) => [
                    'id' => $r->id,
                    'versionName' => $r->version_name,
                    'versionCode' => $r->version_code,
                    'releaseDate' => $r->release_date,
                    'isForceUpdate' => $r->is_force_update,
                    'createdAt' => $r->created_at ? $r->created_at->format('Y-m-d') : date('Y-m-d'),
                ]),
                'systemStats' => [
                    'totalKosts' => $kosts->count(),
                    'totalUsers' => $users->count(),
                    'activeAds' => $ads->where('is_active', true)->count(),
                ],
                'system' => [
                    'appName' => $settings['app_name'] ?? 'CariKostKita',
                    'contactPhone' => $settings['contact_whatsapp'] ?? '0761-555-0123',
                    'supportEmail' => $settings['support_email'] ?? 'support@carikostkita.id',
                ],
                'readNotifications' => [],
                'notifications' => [], // Add mock notifications if needed
            ]
        ];

        return response()->json($data);
    }
}
