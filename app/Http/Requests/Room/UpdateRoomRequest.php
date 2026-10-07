<?php

namespace App\Http\Requests\Room;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'room_type' => ['nullable', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'size' => ['nullable', 'string', 'max:50'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string'],
            'total_rooms' => ['sometimes', 'integer', 'min:1'],
            'available_rooms' => ['sometimes', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:available,full'],
        ];
    }
}
