<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KostResource;
use App\Http\Resources\ReportResource;
use App\Http\Resources\UserResource;
use App\Models\Kost;
use App\Models\Report;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
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
                    ->orWhere('email', 'like', "%{$search}%");
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
     * List all kosts (Admin only).
     */
    public function kosts(Request $request): JsonResponse
    {
        $query = Kost::with(['owner', 'primaryImage']);

        if ($status = $request->query('verification_status')) {
            $query->where('verification_status', $status);
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
     * List only pending kosts awaiting verification.
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

        $kost->delete();

        return $this->successResponse(null, 'Kost berhasil dihapus oleh admin');
    }

    /**
     * List reports.
     */
    public function reports(Request $request): JsonResponse
    {
        $query = Report::with(['reporter', 'kost']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
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
     * Update report status.
     */
    public function updateReportStatus(Request $request, Report $report): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,reviewed,resolved'],
        ]);

        $report->update(['status' => $validated['status']]);
        $report->load(['reporter', 'kost']);

        return $this->successResponse(new ReportResource($report), 'Status laporan berhasil diperbarui');
    }
}
