<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'owner_id' => $this->owner_id,
            'kost_id' => $this->kost_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'kost' => new KostResource($this->whenLoaded('kost')),
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'unread_count' => $this->when(
                isset($this->unread_count),
                fn () => (int) $this->unread_count
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
