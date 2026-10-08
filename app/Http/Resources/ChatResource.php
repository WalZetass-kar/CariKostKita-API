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
        $currentUser = $request->user();
        $unread = 0;
        if ($currentUser) {
            if ($currentUser->id === $this->owner_id) {
                $unread = $this->unread_count_owner ?? 0;
            } else {
                $unread = $this->unread_count_user ?? 0;
            }
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'pencari_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'kost_id' => $this->kost_id,
            'last_message' => $this->last_message ?? $this->latestMessage?->content,
            'last_message_at' => $this->last_message_at?->toISOString() ?? $this->latestMessage?->created_at?->toISOString(),
            'unread_count' => $unread,
            'unread_count_user' => (int) ($this->unread_count_user ?? 0),
            'unread_count_owner' => (int) ($this->unread_count_owner ?? 0),
            'user' => new UserResource($this->whenLoaded('user')),
            'pencari' => new UserResource($this->whenLoaded('pencari') ?? $this->whenLoaded('user')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'kost' => new KostResource($this->whenLoaded('kost')),
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
