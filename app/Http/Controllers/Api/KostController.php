<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kost\StoreKostRequest;
use App\Http\Requests\Kost\UpdateKostRequest;
use App\Http\Resources\KostResource;
use App\Models\Kost;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KostController extends Controller
{
    /**
     * Display a listing of public approved kosts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Kost::with(['owner', 'primaryImage'])
            ->approved();

        // Search filter
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        // District filter
        if ($district = $request->query('district')) {
            $query->where('district', 'like', "%{$district}%");
        }

        // City filter
        if ($city = $request->query('city')) {
            $query->where('city', 'like', "%{$city}%");
        }

        // Gender filter
        if ($gender = $request->query('gender')) {
            $query->where('gender_type', $gender);
        }

        // Price range filters
        if ($minPrice = $request->query('min_price')) {
            $query->where('price_max', '>=', (float) $minPrice);
        }
        if ($maxPrice = $request->query('max_price')) {
            $query->where('price_min', '<=', (float) $maxPrice);
        }

        $perPage = min(max($request->integer('per_page', 15), 1), 50);
        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data kost berhasil ditemukan',
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
     * Display the specified kost.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $kost = Kost::with(['owner', 'images', 'rooms'])->findOrFail($id);

        $currentUser = $request->user('sanctum');

        // Non-approved kosts can only be viewed by their owner or an admin
        if ($kost->verification_status !== 'approved') {
            if (! $currentUser || (! $currentUser->isAdmin() && $currentUser->id !== $kost->owner_id)) {
                return $this->errorResponse('Kost belum disetujui atau tidak tersedia.', 404);
            }
        }

        return $this->successResponse(new KostResource($kost), 'Detail kost berhasil diambil');
    }

    /**
     * Store a newly created kost in storage (Owner only).
     */
    public function store(StoreKostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['owner_id'] = $request->user()->id;
        $validated['verification_status'] = 'pending'; // Kost baru menunggu verifikasi admin

        $kost = Kost::create($validated);
        $kost->load(['owner']);

        return $this->successResponse(new KostResource($kost), 'Kost berhasil didaftarkan dan menunggu verifikasi admin', 201);
    }

    /**
     * Update the specified kost (Owner only).
     */
    public function update(UpdateKostRequest $request, Kost $kost): JsonResponse
    {
        Gate::authorize('update', $kost);

        $kost->update($request->validated());
        $kost->load(['owner', 'primaryImage', 'rooms']);

        return $this->successResponse(new KostResource($kost), 'Data kost berhasil diperbarui');
    }

    /**
     * Remove the specified kost (Owner only).
     */
    public function destroy(Request $request, Kost $kost, SupabaseStorageService $storageService): JsonResponse
    {
        Gate::authorize('delete', $kost);

        // Delete images from storage
        foreach ($kost->images as $image) {
            $storageService->deleteImage($image->file_path);
        }

        $kost->delete();

        return $this->successResponse(null, 'Kost berhasil dihapus');
    }

    /**
     * List all kosts owned by the authenticated owner.
     */
    public function myKosts(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 50);

        $paginated = Kost::with(['primaryImage', 'rooms'])
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kost pemilik berhasil diambil',
            'data' => KostResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }
}
