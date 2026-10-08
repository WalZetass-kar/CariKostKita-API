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

    protected function prepareForValidation(): void
    {
        // Support single price field
        $singlePrice = $this->input('price') ?? $this->input('harga');
        if ($singlePrice !== null) {
            $this->merge([
                'price_min' => $this->input('price_min') ?? $singlePrice,
                'price_max' => $this->input('price_max') ?? $singlePrice,
            ]);
        }

        // Support name aliases
        if (! $this->has('name') && ($this->has('nama_kost') || $this->has('namaKost'))) {
            $this->merge([
                'name' => $this->input('nama_kost') ?? $this->input('namaKost'),
            ]);
        }

        // Support address aliases
        if (! $this->has('address') && $this->has('alamat')) {
            $this->merge([
                'address' => $this->input('alamat'),
            ]);
        }

        // Support district aliases
        if (! $this->has('district') && ($this->has('kecamatan') || $this->has('area'))) {
            $this->merge([
                'district' => $this->input('kecamatan') ?? $this->input('area'),
            ]);
        }

        // Support gender aliases
        $gender = $this->input('gender_type') ?? $this->input('tipeKost') ?? $this->input('gender');
        if ($gender) {
            $this->merge([
                'gender_type' => strtolower($gender),
            ]);
        }

        // Support facilities aliases
        if (! $this->has('facilities') && $this->has('fasilitas')) {
            $this->merge([
                'facilities' => $this->input('fasilitas'),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'nama_kost' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['sometimes', 'string'],
            'patokan' => ['nullable', 'string', 'max:255'],
            'no_whatsapp' => ['nullable', 'string', 'max:20'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'district' => ['sometimes', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'gender_type' => ['sometimes', 'string', 'in:putra,putri,campur'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string'],
            'fasilitas' => ['nullable', 'array'],
            'aturan' => ['nullable', 'array'],
            'ukuran_kamar' => ['nullable', 'string', 'max:50'],
            'total_kamar' => ['nullable', 'integer', 'min:1'],
            'kamar_tersedia' => ['nullable', 'integer', 'min:0'],
            'thumbnail_url' => ['nullable', 'string', 'max:2048'],
            'image_urls' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:available,full,Aktif,Nonaktif'],
        ];
    }
}
