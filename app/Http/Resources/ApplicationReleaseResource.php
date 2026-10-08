<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationReleaseResource extends JsonResource
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
            'version_name' => $this->version_name,
            'version_code' => (int) $this->version_code,
            'apk_url' => $this->apk_url,
            'file_size' => $this->file_size,
            'min_android' => $this->min_android,
            'is_force_update' => (bool) $this->is_force_update,
            'is_latest' => (bool) $this->is_latest,
            'changelog' => $this->changelog,
            'release_date' => $this->release_date?->toDateString(),
            'download_count' => (int) $this->download_count,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
