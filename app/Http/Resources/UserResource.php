<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'no_hp' => $this->no_hp ?? $this->phone,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url ?? $this->avatar,
            'bio' => $this->bio ?? $this->profile?->bio,
            'verification_status' => $this->verification_status ?? 'unverified',
            'catatan_revisi' => $this->catatan_revisi,
            'is_active' => (bool) ($this->is_active ?? true),
            'is_admin' => $this->isAdmin(),
            'is_pemilik' => $this->isPemilik(),
            'is_pencari' => $this->isPencari(),
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
