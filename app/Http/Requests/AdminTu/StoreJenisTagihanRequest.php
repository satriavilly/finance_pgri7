<?php

namespace App\Http\Requests\AdminTu;

use App\Models\KategoriTagihan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJenisTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin_tu');
    }

    public function rules(): array
    {
        return [
            'kelas_ids'   => ['required', 'array', 'min:1'],
            'kelas_ids.*' => ['exists:kelas,id'],
            'nama'        => ['required', 'string', 'max:255'],
            'deskripsi'   => ['nullable', 'string', 'max:1000'],
            'kategori'    => ['required', Rule::exists('kategori_tagihan', 'kode')],
            'total_nominal' => ['required', 'numeric', 'min:1000'],
            'due_date'    => ['nullable', 'date', 'after:today'],
            'is_cicilan'  => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kelas_ids.required'     => 'Pilih minimal satu kelas.',
            'nama.required'          => 'Nama tagihan wajib diisi.',
            'total_nominal.required' => 'Nominal tagihan wajib diisi.',
            'total_nominal.min'      => 'Nominal minimal Rp 1.000.',
            'due_date.after'         => 'Jatuh tempo harus setelah hari ini.',
            'kategori.exists'        => 'Kategori tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_cicilan' => $this->boolean('is_cicilan'),
        ]);
    }
}
