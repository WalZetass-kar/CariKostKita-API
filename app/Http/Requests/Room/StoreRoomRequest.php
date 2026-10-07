<?php

namespace App\Http\Requests\Room;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'room_type' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'size' => ['nullable', 'string', 'max:50'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string'],
            'total_rooms' => ['required', 'integer', 'min:1'],
            'available_rooms' => ['required', 'integer', 'min:0', 'lte:total_rooms'],
            'status' => ['nullable', 'string', 'in:available,full'],
        ];
    }
}
