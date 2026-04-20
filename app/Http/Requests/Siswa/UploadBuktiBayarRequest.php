<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class UploadBuktiBayarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('siswa');
    }

    public function rules(): array
    {
        return [
            'nominal' => ['required', 'numeric', 'min:1000'],
            'metode' => ['required', 'in:transfer,qris'],
            'cicilan_id' => ['nullable', 'exists:cicilan,id'],
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_bayar.required' => 'Bukti bayar wajib diupload.',
            'bukti_bayar.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'bukti_bayar.max' => 'Ukuran file maksimal 2MB.',
            'metode.required' => 'Metode pembayaran wajib dipilih.',
        ];
    }
}
