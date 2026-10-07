<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
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
            'user_id' => $this->user_id,
            'kost_id' => $this->kost_id,
            'kost' => new KostResource($this->whenLoaded('kost')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
