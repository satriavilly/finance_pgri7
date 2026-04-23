<?php

namespace App\Http\Requests\AdminTu;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin_tu');
    }

    public function rules(): array
    {
        return [
            'kelas_ids'      => ['required', 'array', 'min:1'],
            'kelas_ids.*'    => ['exists:kelas,id'],
            'nama'           => ['required', 'string', 'max:255'],
            'deskripsi'      => ['nullable', 'string', 'max:1000'],
            'kategori'       => ['required', 'in:kas_kelas,buku_lks,kegiatan,seragam,lainnya'],
            'total_nominal'  => ['required', 'numeric', 'min:1000'],
            'due_date'       => ['nullable', 'date', 'after:today'],
            'is_cicilan'     => ['boolean'],
            'jumlah_cicilan' => ['required_if:is_cicilan,1', 'nullable', 'integer', 'min:2', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'kelas_ids.required'           => 'Pilih minimal satu kelas.',
            'nama.required'                => 'Nama tagihan wajib diisi.',
            'total_nominal.required'       => 'Nominal tagihan wajib diisi.',
            'total_nominal.min'            => 'Nominal minimal Rp 1.000.',
            'due_date.after'               => 'Jatuh tempo harus setelah hari ini.',
            'jumlah_cicilan.required_if'   => 'Jumlah cicilan wajib diisi jika memilih cicilan.',
            'jumlah_cicilan.min'           => 'Minimal 2 cicilan.',
            'jumlah_cicilan.max'           => 'Maksimal 12 cicilan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_cicilan' => $this->boolean('is_cicilan'),
        ]);
    }
}
