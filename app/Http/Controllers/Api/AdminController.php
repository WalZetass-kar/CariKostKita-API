<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kost\StoreKostRequest;
use App\Http\Requests\Kost\UpdateKostRequest;
use App\Http\Resources\AdvertisementResource;
use App\Http\Resources\ApplicationReleaseResource;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\KostResource;
use App\Http\Resources\ReportResource;
use App\Http\Resources\UserResource;
use App\Models\Advertisement;
use App\Models\ApplicationRelease;
use App\Models\AuditLog;
use App\Models\Kost;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Models\User;
use App\Models\UserReport;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin Dashboard analytics summary for Admin-Gue.
     */
    public function dashboard(): JsonResponse
    {
        $totalKosts = Kost::count();
        $kostAktif = Kost::whereIn('verification_status', ['approved', 'Aktif'])->count();
        $menungguVerifikasi = Kost::where('verification_status', 'pending')->count();
        $ditolak = Kost::whereIn('verification_status', ['rejected', 'Ditolak'])->count();
        $nonaktif = Kost::whereIn('status', ['full', 'Nonaktif'])->count();

        $totalOwners = User::whereIn('role', ['pemilik', 'owner', 'pemilik_kost'])->count();
        $totalUsers = User::whereIn('role', ['pencari', 'user'])->count();
        $laporanBaru = Report::whereIn('status', ['pending', 'Baru'])->count();
        $iklanAktif = Advertisement::where('is_active', true)->count();

        // Area distribution in Pekanbaru
        $areaDistribution = Kost::selectRaw('district, count(*) as count')
            ->groupBy('district')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Ad telemetry summary
        $totalAdImpressions = (int) Advertisement::sum('impressions');
        $totalAdClicks = (int) Advertisement::sum('clicks');
        $averageCtr = $totalAdImpressions > 0
            ? round(($totalAdClicks / $totalAdImpressions) * 100, 2).'%'
            : '0%';

        return $this->successResponse([
            'metrics' => [
                'total_kost' => $totalKosts,
                'kost_aktif' => $kostAktif,
                'menunggu_verifikasi' => $menungguVerifikasi,
                'ditolak' => $ditolak,
                'nonaktif' => $nonaktif,
                'total_owners' => $totalOwners,
                'total_users' => $totalUsers,
                'laporan_baru' => $laporanBaru,
                'iklan_aktif' => $iklanAktif,
            ],
            'ads_overview' => [
                'total_impressions' => $totalAdImpressions,
                'total_clicks' => $totalAdClicks,
                'average_ctr' => $averageCtr,
            ],
            'area_distribution' => $areaDistribution,
        ], 'Ringkasan dashboard admin berhasil diambil');
    }

    /**
     * List all users (Admin only).
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::with('profile');

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengguna berhasil diambil',
            'data' => UserResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * List all kost owners with properties count.
     */
    public function owners(Request $request): JsonResponse
    {
        $query = User::with('profile')
            ->withCount('kosts')
            ->whereIn('role', ['pemilik', 'owner', 'pemilik_kost']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('verification_status')) {
            $query->where('verification_status', $status);
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pemilik kost berhasil diambil',
            'data' => UserResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Toggle active status of a user / owner account.
     */
    public function toggleUserStatus($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLog::record(
            request()->user(),
            $user->is_active ? 'aktifkan_pengguna' : 'nonaktifkan_pengguna',
            'user',
            $user->id,
            ['name' => $user->name, 'email' => $user->email, 'is_active' => $user->is_active]
        );

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return $this->successResponse(
            new UserResource($user),
            "Akun {$user->name} berhasil {$statusStr}"
        );
    }

    /**
     * List all kosts for Admin (with status & area filters).
     */
    public function kosts(Request $request): JsonResponse
    {
        $query = Kost::with(['owner', 'primaryImage', 'images', 'rooms']);

        // Status filter (Indonesian & English mappings)
        if ($status = $request->query('status') ?? $request->query('verification_status')) {
            $statusMap = [
                'Menunggu Verifikasi' => 'pending',
                'Aktif' => 'approved',
                'Ditolak' => 'rejected',
                'Nonaktif' => 'Nonaktif',
            ];
            $dbStatus = $statusMap[$status] ?? $status;

            if ($dbStatus === 'approved') {
                $query->whereIn('verification_status', ['approved', 'Aktif']);
            } elseif ($dbStatus === 'rejected') {
                $query->whereIn('verification_status', ['rejected', 'Ditolak']);
            } else {
                $query->where('verification_status', $dbStatus);
            }
        }

        // Area / District filter
        $area = $request->query('area') ?? $request->query('district') ?? $request->query('kecamatan');
        if ($area) {
            $query->where('district', 'like', "%{$area}%");
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nama_kost', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar seluruh kost berhasil diambil',
            'data' => KostResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Show single kost detail for admin.
     */
    public function showKost($id): JsonResponse
    {
        $kost = Kost::with(['owner', 'images', 'rooms', 'reviews.user'])->findOrFail($id);

        return $this->successResponse(new KostResource($kost), 'Detail kost berhasil diambil');
    }

    /**
     * Store kost created directly by admin.
     */
    public function storeKost(StoreKostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['owner_id'] = $request->input('owner_id', $request->user()->id);
        $validated['verification_status'] = $request->input('verification_status', 'approved');

        $kost = Kost::create($validated);
        $kost->load(['owner', 'primaryImage']);

        AuditLog::record($request->user(), 'tambah_kost', 'kost', $kost->id, ['name' => $kost->name]);

        return $this->successResponse(new KostResource($kost), 'Kost berhasil ditambahkan oleh admin', 201);
    }

    /**
     * Update kost details by admin.
     */
    public function updateKost(UpdateKostRequest $request, $id): JsonResponse
    {
        $kost = Kost::findOrFail($id);
        $kost->update($request->validated());
        $kost->load(['owner', 'primaryImage', 'rooms']);

        AuditLog::record($request->user(), 'update_kost', 'kost', $kost->id, ['name' => $kost->name]);

        return $this->successResponse(new KostResource($kost), 'Data kost berhasil diperbarui');
    }

    /**
     * Update kost verification/active status from Admin-Gue (Verifikasi, Tolak, Nonaktifkan).
     */
    public function updateKostStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'catatan_revisi' => ['nullable', 'string', 'max:1000'],
        ]);

        $kost = Kost::findOrFail($id);

        // Normalize status
        $rawStatus = $validated['status'];
        $newVerificationStatus = match ($rawStatus) {
            'Aktif', 'approved' => 'approved',
            'Ditolak', 'rejected' => 'rejected',
            'Menunggu Verifikasi', 'pending' => 'pending',
            default => 'rejected',
        };

        $kost->update([
            'verification_status' => $newVerificationStatus,
            'catatan_revisi' => $validated['catatan_revisi'] ?? null,
            'status' => $rawStatus === 'Nonaktif' ? 'full' : 'available',
        ]);

        $kost->load(['owner', 'primaryImage']);

        AuditLog::record(
            $request->user(),
            "ubah_status_kost_{$newVerificationStatus}",
            'kost',
            $kost->id,
            ['status' => $newVerificationStatus, 'catatan_revisi' => $kost->catatan_revisi]
        );

        return $this->successResponse(new KostResource($kost), 'Status kost berhasil diperbarui');
    }

    /**
     * List pending kosts.
     */
    public function pendingKosts(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 100);

        $paginated = Kost::with(['owner', 'primaryImage', 'rooms'])
            ->where('verification_status', 'pending')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kost menunggu verifikasi berhasil diambil',
            'data' => KostResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Approve a kost.
     */
    public function approveKost($id): JsonResponse
    {
        $kost = Kost::findOrFail($id);
        $kost->update(['verification_status' => 'approved']);
        $kost->load(['owner', 'primaryImage']);

        AuditLog::record(request()->user(), 'verifikasi_kost', 'kost', $kost->id, ['name' => $kost->name]);

        return $this->successResponse(new KostResource($kost), 'Kost berhasil disetujui');
    }

    /**
     * Reject a kost.
     */
    public function rejectKost($id): JsonResponse
    {
        $kost = Kost::findOrFail($id);
        $kost->update(['verification_status' => 'rejected']);
        $kost->load(['owner', 'primaryImage']);

        AuditLog::record(request()->user(), 'tolak_kost', 'kost', $kost->id, ['name' => $kost->name]);

        return $this->successResponse(new KostResource($kost), 'Kost berhasil ditolak');
    }

    /**
     * Delete a kost as admin.
     */
    public function destroyKost($id, SupabaseStorageService $storageService): JsonResponse
    {
        $kost = Kost::findOrFail($id);

        foreach ($kost->images as $image) {
            $storageService->deleteImage($image->file_path);
        }

        $kostName = $kost->name;
        $kostId = $kost->id;
        $kost->delete();

        AuditLog::record(request()->user(), 'hapus_kost', 'kost', $kostId, ['name' => $kostName]);

        return $this->successResponse(null, 'Kost berhasil dihapus oleh admin');
    }

    /**
     * List reports (Baru, Diproses, Selesai, Ditolak).
     */
    public function reports(Request $request): JsonResponse
    {
        $query = Report::with(['reporter', 'kost']);

        if ($status = $request->query('status')) {
            $statusMap = [
                'Baru' => 'pending',
                'Diproses' => 'reviewed',
                'Selesai' => 'resolved',
            ];
            $dbStatus = $statusMap[$status] ?? $status;
            $query->where('status', $dbStatus);
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan berhasil diambil',
            'data' => ReportResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Update report status (Baru, Diproses, Selesai, Ditolak).
     */
    public function updateReportStatus(Request $request, Report $report): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $status = $validated['status'];
        $dbStatus = match ($status) {
            'Baru' => 'pending',
            'Diproses' => 'reviewed',
            'Selesai' => 'resolved',
            default => $status,
        };

        $report->update(['status' => $dbStatus]);
        $report->load(['reporter', 'kost']);

        AuditLog::record(
            $request->user(),
            'tindak_lanjut_laporan',
            'report',
            $report->id,
            ['status' => $dbStatus]
        );

        return $this->successResponse(new ReportResource($report), 'Status laporan berhasil diperbarui');
    }

    /**
     * List user safety reports.
     */
    public function userReports(Request $request): JsonResponse
    {
        $query = UserReport::with(['reporter', 'reportedUser']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan pelanggaran pengguna berhasil diambil',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Update user report status.
     */
    public function updateUserReportStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Baru,Diproses,Selesai,Ditolak'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = UserReport::findOrFail($id);
        $report->update($validated);

        AuditLog::record(
            $request->user(),
            'proses_laporan_pengguna',
            'user_report',
            $report->id,
            $validated
        );

        return $this->successResponse($report, 'Status laporan pengguna berhasil diperbarui');
    }

    /**
     * List advertisements for Admin-Gue.
     */
    public function ads(Request $request): JsonResponse
    {
        $query = Advertisement::with('kost');

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar iklan berhasil diambil',
            'data' => AdvertisementResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Create advertisement with optional banner upload.
     */
    public function storeAd(Request $request, SupabaseStorageService $storageService): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:Banner,Pop-up,Promo Kost'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'banner_file' => ['nullable', 'image', 'max:5120'],
            'target_url' => ['nullable', 'string', 'max:2048'],
            'kost_id' => ['nullable', 'exists:kosts,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('banner_file')) {
            $upload = $storageService->uploadAdBanner($request->file('banner_file'));
            $imageUrl = $upload['url'];
        }

        if (! $imageUrl) {
            $imageUrl = 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1200&q=80';
        }

        $ad = Advertisement::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'image_url' => $imageUrl,
            'target_url' => $validated['target_url'] ?? null,
            'kost_id' => $validated['kost_id'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        AuditLog::record($request->user(), 'tambah_iklan', 'ad', $ad->id, ['title' => $ad->title]);

        return $this->successResponse(new AdvertisementResource($ad), 'Iklan berhasil ditambahkan', 201);
    }

    /**
     * Update advertisement.
     */
    public function updateAd(Request $request, $id, SupabaseStorageService $storageService): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:Banner,Pop-up,Promo Kost'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'banner_file' => ['nullable', 'image', 'max:5120'],
            'target_url' => ['nullable', 'string', 'max:2048'],
            'kost_id' => ['nullable', 'exists:kosts,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('banner_file')) {
            $upload = $storageService->uploadAdBanner($request->file('banner_file'));
            $validated['image_url'] = $upload['url'];
        }

        $ad->update($validated);

        AuditLog::record($request->user(), 'update_iklan', 'ad', $ad->id, ['title' => $ad->title]);

        return $this->successResponse(new AdvertisementResource($ad), 'Iklan berhasil diperbarui');
    }

    /**
     * Delete advertisement.
     */
    public function deleteAd($id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);
        $title = $ad->title;
        $ad->delete();

        AuditLog::record(request()->user(), 'hapus_iklan', 'ad', $id, ['title' => $title]);

        return $this->successResponse(null, 'Iklan berhasil dihapus');
    }

    /**
     * Toggle advertisement active status.
     */
    public function toggleAdStatus($id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);
        $ad->is_active = ! $ad->is_active;
        $ad->save();

        AuditLog::record(
            request()->user(),
            $ad->is_active ? 'aktifkan_iklan' : 'nonaktifkan_iklan',
            'ad',
            $ad->id,
            ['title' => $ad->title, 'is_active' => $ad->is_active]
        );

        return $this->successResponse(
            new AdvertisementResource($ad),
            'Status iklan berhasil diubah'
        );
    }

    /**
     * Get advertisement analytics performance.
     */
    public function adAnalytics(): JsonResponse
    {
        $ads = Advertisement::all();
        $totalImpressions = $ads->sum('impressions');
        $totalClicks = $ads->sum('clicks');

        return $this->successResponse([
            'total_ads' => $ads->count(),
            'active_ads' => $ads->where('is_active', true)->count(),
            'total_impressions' => $totalImpressions,
            'total_clicks' => $totalClicks,
            'average_ctr' => $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2).'%' : '0%',
            'ads' => AdvertisementResource::collection($ads),
        ], 'Statistik performa iklan berhasil diambil');
    }

    /**
     * List audit logs for admin activity history.
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $query = AuditLog::with('admin');

        if ($action = $request->query('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        $perPage = min(max($request->integer('per_page', 25), 1), 100);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat audit log berhasil diambil',
            'data' => AuditLogResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * List application releases.
     */
    public function releases(Request $request): JsonResponse
    {
        $releases = ApplicationRelease::latest('version_code')->get();

        return $this->successResponse(
            ApplicationReleaseResource::collection($releases),
            'Daftar rilis aplikasi berhasil diambil'
        );
    }

    /**
     * Store new application release (with APK upload).
     */
    public function storeRelease(Request $request, SupabaseStorageService $storageService): JsonResponse
    {
        $validated = $request->validate([
            'version_name' => ['required', 'string', 'max:50'],
            'version_code' => ['required', 'integer'],
            'apk_url' => ['nullable', 'string', 'max:2048'],
            'apk_file' => ['nullable', 'file', 'max:102400'], // max 100MB
            'file_size' => ['nullable', 'string', 'max:50'],
            'min_android' => ['nullable', 'string', 'max:100'],
            'is_force_update' => ['nullable', 'boolean'],
            'is_latest' => ['nullable', 'boolean'],
            'changelog' => ['nullable', 'string'],
            'release_date' => ['required', 'date'],
        ]);

        $apkUrl = $validated['apk_url'] ?? null;
        if ($request->hasFile('apk_file')) {
            $upload = $storageService->uploadApk($request->file('apk_file'), $validated['version_name']);
            $apkUrl = $upload['url'];
            $validated['file_size'] = round($request->file('apk_file')->getSize() / (1024 * 1024), 1).' MB';
        }

        if (! $apkUrl) {
            $apkUrl = "https://github.com/g-423/KosKita/releases/download/{$validated['version_name']}/CariKostKita-{$validated['version_name']}.apk";
        }

        if (! empty($validated['is_latest'])) {
            ApplicationRelease::query()->update(['is_latest' => false]);
        }

        $release = ApplicationRelease::create([
            'version_name' => $validated['version_name'],
            'version_code' => $validated['version_code'],
            'apk_url' => $apkUrl,
            'file_size' => $validated['file_size'] ?? '18.4 MB',
            'min_android' => $validated['min_android'] ?? 'Android 8.0 (Oreo)',
            'is_force_update' => $validated['is_force_update'] ?? false,
            'is_latest' => $validated['is_latest'] ?? true,
            'changelog' => $validated['changelog'] ?? null,
            'release_date' => $validated['release_date'],
        ]);

        AuditLog::record($request->user(), 'rilis_aplikasi_baru', 'release', $release->id, ['version' => $release->version_name]);

        return $this->successResponse(
            new ApplicationReleaseResource($release),
            'Rilis aplikasi baru berhasil dipublikasikan',
            201
        );
    }

    /**
     * Get platform settings list.
     */
    public function settings(): JsonResponse
    {
        $settings = PlatformSetting::all();

        return $this->successResponse($settings, 'Pengaturan platform berhasil diambil');
    }

    /**
     * Update platform settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($validated['settings'] as $key => $val) {
            PlatformSetting::set($key, $val);
        }

        AuditLog::record($request->user(), 'update_pengaturan_platform', 'settings', null, $validated['settings']);

        return $this->successResponse(
            PlatformSetting::all(),
            'Pengaturan platform berhasil diperbarui'
        );
    }
}
