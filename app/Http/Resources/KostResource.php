<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resolve images array
        $imageUrls = $this->image_urls ?? [];
        if (empty($imageUrls) && $this->relationLoaded('images')) {
            $imageUrls = $this->images->pluck('file_path')->all();
        }

        // Resolve thumbnail
        $thumbnail = $this->thumbnail_url;
        if (! $thumbnail && $this->relationLoaded('primaryImage') && $this->primaryImage) {
            $thumbnail = $this->primaryImage->file_path;
        } elseif (! $thumbnail && ! empty($imageUrls)) {
            $thumbnail = $imageUrls[0];
        }

        $facilities = $this->fasilitas ?? $this->facilities ?? [];

        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,

            // Naming aliases (compatible with Mobile App, Admin-Gue, & standard REST)
            'name' => $this->name,
            'nama_kost' => $this->nama_kost ?? $this->name,
            'namaKost' => $this->nama_kost ?? $this->name,

            'description' => $this->description,
            'deskripsi' => $this->description,

            'address' => $this->address,
            'alamat' => $this->address,
            'patokan' => $this->patokan,

            'province' => $this->province,
            'city' => $this->city,
            'kota' => $this->city,
            'district' => $this->district,
            'kecamatan' => $this->district,
            'area' => $this->district,
            'village' => $this->village,

            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,

            // Price fields
            'price' => (float) $this->price_min,
            'harga' => (float) $this->price_min,
            'price_min' => (float) $this->price_min,
            'price_max' => (float) $this->price_max,
            'deposit' => (float) ($this->deposit ?? 0),

            // Types and specifications
            'gender_type' => $this->gender_type,
            'tipeKost' => strtoupper($this->gender_type),
            'ukuran_kamar' => $this->ukuran_kamar,
            'ukuranKamar' => $this->ukuran_kamar,
            'total_kamar' => (int) ($this->total_kamar ?? 1),
            'totalKamar' => (int) ($this->total_kamar ?? 1),
            'kamar_tersedia' => (int) ($this->kamar_tersedia ?? 1),
            'kamarTersedia' => (int) ($this->kamar_tersedia ?? 1),

            // Facilities & Rules
            'facilities' => $facilities,
            'fasilitas' => $facilities,
            'aturan' => $this->aturan ?? [],

            // Contact
            'no_whatsapp' => $this->no_whatsapp ?? $this->owner?->phone,
            'noWhatsapp' => $this->no_whatsapp ?? $this->owner?->phone,

            // Media
            'thumbnail_url' => $thumbnail,
            'thumbnailUrl' => $thumbnail,
            'image_urls' => $imageUrls,
            'imageUrls' => $imageUrls,

            // Ratings & Telemetry
            'rating' => (float) ($this->rating_avg ?? 0.0),
            'rating_avg' => (float) ($this->rating_avg ?? 0.0),
            'rating_count' => (int) ($this->rating_count ?? 0),
            'views_count' => (int) ($this->views_count ?? 0),

            // Statuses
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'verificationStatus' => $this->verification_status,
            'catatan_revisi' => $this->catatan_revisi,
            'catatanRevisi' => $this->catatan_revisi,
            'location_verification' => $this->location_verification ?? 'UNVERIFIED',

            // Relationships
            'owner' => new UserResource($this->whenLoaded('owner')),
            'primary_image' => new KostImageResource($this->whenLoaded('primaryImage')),
            'images' => KostImageResource::collection($this->whenLoaded('images')),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
