<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use App\Models\Kost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of user favorites.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 50);

        $paginated = Favorite::with(['kost.owner', 'kost.primaryImage', 'kost.rooms'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar favorit berhasil diambil',
            'data' => FavoriteResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Add a kost to user favorites.
     */
    public function store(Request $request, Kost $kost): JsonResponse
    {
        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'kost_id' => $kost->id,
        ]);

        $favorite->load(['kost.owner', 'kost.primaryImage']);

        return $this->successResponse(new FavoriteResource($favorite), 'Kost berhasil ditambahkan ke favorit', 201);
    }

    /**
     * Remove a kost from user favorites.
     */
    public function destroy(Request $request, Kost $kost): JsonResponse
    {
        Favorite::where('user_id', $request->user()->id)
            ->where('kost_id', $kost->id)
            ->delete();

        return $this->successResponse(null, 'Kost berhasil dihapus dari favorit');
    }
}
