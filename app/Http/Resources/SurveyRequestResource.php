<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyRequestResource extends JsonResource
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
            'kost_name' => $this->kost?->nama_kost ?? $this->kost?->name,
            'kost_thumbnail' => $this->kost?->thumbnail_url,
            'pencari_id' => $this->pencari_id,
            'pencari_name' => $this->pencari?->name,
            'pencari_phone' => $this->pencari?->no_hp ?? $this->pencari?->phone,
            'owner_id' => $this->owner_id,
            'owner_name' => $this->owner?->name,
            'survey_date' => $this->survey_date?->toISOString(),
            'status' => $this->status, // PENDING, CONFIRMED, REJECTED, COMPLETED, CANCELLED
            'notes' => $this->notes,
            'owner_notes' => $this->owner_notes,
            'has_reviewed' => (bool) $this->review,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
