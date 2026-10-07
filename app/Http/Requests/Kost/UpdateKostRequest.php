<?php

namespace App\Http\Requests\Kost;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized via controller policy check
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['sometimes', 'string'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'district' => ['sometimes', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric'],
            'gender_type' => ['sometimes', 'string', 'in:putra,putri,campur'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string'],
            'status' => ['nullable', 'string', 'in:available,full'],
        ];
    }
}
