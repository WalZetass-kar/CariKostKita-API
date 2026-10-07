<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'kost_id' => $this->kost_id,
            'name' => $this->name,
            'room_type' => $this->room_type,
            'price' => (float) $this->price,
            'size' => $this->size,
            'facilities' => $this->facilities,
            'total_rooms' => (int) $this->total_rooms,
            'available_rooms' => (int) $this->available_rooms,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
