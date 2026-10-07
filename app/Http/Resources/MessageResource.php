<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'message' => $this->message,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
