<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KostImageResource extends JsonResource
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
            'file_path' => $this->file_path,
            'url' => $this->url,
            'is_primary' => (bool) $this->is_primary,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
