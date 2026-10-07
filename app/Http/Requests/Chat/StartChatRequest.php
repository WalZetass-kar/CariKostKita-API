<?php

namespace App\Http\Requests\Chat;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
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
            'kost_id' => ['required', 'integer', 'exists:kosts,id'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kost_id.required' => 'ID Kost wajib disertakan.',
            'kost_id.exists' => 'Kost tidak ditemukan.',
        ];
    }
}
