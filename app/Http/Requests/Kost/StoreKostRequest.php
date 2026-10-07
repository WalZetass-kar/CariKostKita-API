<?php

namespace App\Http\Requests\Kost;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPemilik() || $this->user()?->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'price_min' => ['required', 'numeric', 'min:0'],
            'price_max' => ['required', 'numeric', 'gte:price_min'],
            'gender_type' => ['required', 'string', 'in:putra,putri,campur'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string'],
            'status' => ['nullable', 'string', 'in:available,full'],
        ];
    }
}
