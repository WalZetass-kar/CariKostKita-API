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
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'village' => $this->village,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'price_min' => (float) $this->price_min,
            'price_max' => (float) $this->price_max,
            'gender_type' => $this->gender_type,
            'facilities' => $this->facilities,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'primary_image' => new KostImageResource($this->whenLoaded('primaryImage')),
            'images' => KostImageResource::collection($this->whenLoaded('images')),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
