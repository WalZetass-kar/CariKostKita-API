<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kost\UploadKostImageRequest;
use App\Http\Resources\KostImageResource;
use App\Models\Kost;
use App\Models\KostImage;
use App\Services\SupabaseStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class KostImageController extends Controller
{
    /**
     * Upload an image for a kost.
     */
    public function store(UploadKostImageRequest $request, Kost $kost, SupabaseStorageService $storageService): JsonResponse
    {
        Gate::authorize('manage', $kost);

        $uploaded = $storageService->uploadKostImage($request->file('image'), $kost->id);

        $hasPrimary = $kost->images()->where('is_primary', true)->exists();
        $isPrimary = $request->boolean('is_primary') || ! $hasPrimary;

        if ($isPrimary) {
            $kost->images()->update(['is_primary' => false]);
        }

        $kostImage = $kost->images()->create([
            'file_path' => $uploaded['file_path'],
            'url' => $uploaded['url'],
            'is_primary' => $isPrimary,
        ]);

        return $this->successResponse(new KostImageResource($kostImage), 'Foto kost berhasil diunggah', 201);
    }

    /**
     * Remove an image from a kost.
     */
    public function destroy(KostImage $image, SupabaseStorageService $storageService): JsonResponse
    {
        $kost = $image->kost;
        Gate::authorize('manage', $kost);

        $wasPrimary = $image->is_primary;

        $storageService->deleteImage($image->file_path);
        $image->delete();

        if ($wasPrimary) {
            $nextImage = $kost->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        return $this->successResponse(null, 'Foto kost berhasil dihapus');
    }

    /**
     * Set image as primary.
     */
    public function setPrimary(KostImage $image): JsonResponse
    {
        $kost = $image->kost;
        Gate::authorize('manage', $kost);

        $kost->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return $this->successResponse(new KostImageResource($image), 'Foto utama berhasil diperbarui');
    }
}
