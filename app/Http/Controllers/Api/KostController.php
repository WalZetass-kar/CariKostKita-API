<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kost\StoreKostRequest;
use App\Http\Requests\Kost\UpdateKostRequest;
use App\Http\Resources\KostResource;
use App\Models\Chat;
use App\Models\Favorite;
use App\Models\Kost;
use App\Models\SurveyRequest;
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
        $query = Kost::with(['owner', 'primaryImage', 'images', 'rooms'])
            ->approved();

        // Search filter (keyword, name, description, address)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nama_kost', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        // District/Area filter
        $district = $request->query('district') ?? $request->query('area') ?? $request->query('kecamatan');
        if ($district) {
            $query->where('district', 'like', "%{$district}%");
        }

        // City filter
        if ($city = $request->query('city') ?? $request->query('kota')) {
            $query->where('city', 'like', "%{$city}%");
        }

        // Gender filter (putra, putri, campur)
        $gender = $request->query('gender') ?? $request->query('tipe_kost') ?? $request->query('gender_type');
        if ($gender) {
            $query->where('gender_type', strtolower($gender));
        }

        // Price range filters
        if ($minPrice = $request->query('min_price')) {
            $query->where('price_max', '>=', (float) $minPrice);
        }
        if ($maxPrice = $request->query('max_price')) {
            $query->where('price_min', '<=', (float) $maxPrice);
        }

        // Available rooms only
        if ($request->boolean('available_only')) {
            $query->where('kamar_tersedia', '>', 0);
        }

        // Sorting
        $sort = $request->query('sort');
        match ($sort) {
            'cheapest', 'harga_termurah' => $query->orderBy('price_min', 'asc'),
            'highest', 'harga_tertinggi' => $query->orderBy('price_min', 'desc'),
            'popular', 'terpopuler' => $query->orderBy('views_count', 'desc'),
            'rating', 'rating_tertinggi' => $query->orderBy('rating_avg', 'desc'),
            default => $query->latest(),
        };

        $perPage = min(max($request->integer('per_page', 15), 1), 50);
        $paginated = $query->paginate($perPage);

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
     * Map-based radius search for mobile app interactive map.
     */
    public function map(Request $request): JsonResponse
    {
        $latitude = $request->float('latitude', 0.507068); // Default Pekanbaru center
        $longitude = $request->float('longitude', 101.447779);
        $radiusKm = min(max($request->float('radius', 10.0), 1.0), 50.0);

        // Fetch approved kosts that have non-null coordinates
        $kosts = Kost::with(['primaryImage'])
            ->approved()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Filter within radius (Haversine formula approximation)
        $filtered = $kosts->filter(function ($kost) use ($latitude, $longitude, $radiusKm) {
            $earthRadius = 6371; // km
            $dLat = deg2rad($kost->latitude - $latitude);
            $dLon = deg2rad($kost->longitude - $longitude);
            $a = sin($dLat / 2) * sin($dLat / 2) +
                cos(deg2rad($latitude)) * cos(deg2rad($kost->latitude)) *
                sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            $kost->distance_km = round($distance, 2);

            return $distance <= $radiusKm;
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Data kost radius peta berhasil diambil',
            'data' => KostResource::collection($filtered),
            'meta' => [
                'center' => ['latitude' => $latitude, 'longitude' => $longitude],
                'radius_km' => $radiusKm,
                'total' => $filtered->count(),
            ],
        ]);
    }

    /**
     * Display the specified kost with reviews and specifications.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $kost = Kost::with(['owner', 'images', 'rooms', 'reviews.user'])->findOrFail($id);

        $currentUser = $request->user('sanctum');

        // Non-approved kosts can only be viewed by their owner or an admin
        if (! in_array($kost->verification_status, ['approved', 'Aktif'], true)) {
            if (! $currentUser || (! $currentUser->isAdmin() && $currentUser->id !== $kost->owner_id)) {
                return $this->errorResponse('Kost belum disetujui atau tidak tersedia.', 404);
            }
        }

        // Increment views count on public detail view
        $kost->increment('views_count');

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
        $validated['nama_kost'] = $validated['nama_kost'] ?? $validated['name'];

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

        $validated = $request->validated();
        if (isset($validated['name']) && ! isset($validated['nama_kost'])) {
            $validated['nama_kost'] = $validated['name'];
        }

        $kost->update($validated);
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

        $paginated = Kost::with(['primaryImage', 'images', 'rooms'])
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

    /**
     * Resubmit a rejected or revision-required kost after edits.
     */
    public function resubmitKost(Request $request, Kost $kost): JsonResponse
    {
        Gate::authorize('update', $kost);

        $kost->update([
            'verification_status' => 'pending',
            'catatan_revisi' => null,
        ]);

        $kost->load(['owner', 'primaryImage', 'rooms']);

        return $this->successResponse(new KostResource($kost), 'Kost berhasil diajukan ulang untuk verifikasi');
    }

    /**
     * Get owner analytics overview (30 days performance).
     */
    public function ownerAnalytics(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        $kosts = Kost::where('owner_id', $ownerId)->get();
        $kostIds = $kosts->pluck('id');

        $totalKosts = $kosts->count();
        $totalViews = (int) $kosts->sum('views_count');
        $totalAvailableRooms = (int) $kosts->sum('kamar_tersedia');
        $totalRooms = (int) $kosts->sum('total_kamar');

        $totalFavorites = Favorite::whereIn('kost_id', $kostIds)->count();
        $totalChats = Chat::where('owner_id', $ownerId)->count();
        $totalSurveys = SurveyRequest::where('owner_id', $ownerId)->count();

        return $this->successResponse([
            'total_kosts' => $totalKosts,
            'total_views' => $totalViews,
            'total_rooms' => $totalRooms,
            'available_rooms' => $totalAvailableRooms,
            'total_favorites' => $totalFavorites,
            'total_chats' => $totalChats,
            'total_surveys' => $totalSurveys,
        ], 'Statistik pemilik properti berhasil diambil');
    }
}
