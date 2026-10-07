<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'reporter_id' => $this->reporter_id,
            'kost_id' => $this->kost_id,
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status,
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'kost' => new KostResource($this->whenLoaded('kost')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
