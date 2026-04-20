<?php

namespace App\Http\Requests\WaliKelas;

use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranTunaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['wali_kelas', 'bendahara']);
    }

    public function rules(): array
    {
        return [
            'nominal' => ['required', 'numeric', 'min:1000'],
            'cicilan_id' => ['nullable', 'exists:cicilan,id'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nominal.required' => 'Nominal pembayaran wajib diisi.',
            'nominal.min' => 'Nominal minimal Rp 1.000.',
        ];
    }
}
