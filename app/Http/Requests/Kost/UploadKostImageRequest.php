<?php

namespace App\Http\Requests\Kost;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadKostImageRequest extends FormRequest
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
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'File gambar wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar yang diizinkan hanya jpeg, png, jpg, atau webp.',
            'image.max' => 'Ukuran gambar maksimal adalah 2MB.',
        ];
    }
}
