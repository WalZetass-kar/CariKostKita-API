<?php

namespace App\Http\Requests\Report;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kost_id.required' => 'Kost yang dilaporkan wajib disertakan.',
            'kost_id.exists' => 'Kost tidak ditemukan.',
            'reason.required' => 'Alasan laporan wajib diisi.',
        ];
    }
}
