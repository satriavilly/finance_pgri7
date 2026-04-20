<?php

namespace App\Http\Requests\WaliKelas;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('wali_kelas');
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kategori' => ['required', 'in:kas_kelas,buku_lks,kegiatan,seragam,lainnya'],
            'total_nominal' => ['required', 'numeric', 'min:1000'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tagihan wajib diisi.',
            'total_nominal.required' => 'Nominal tagihan wajib diisi.',
            'total_nominal.min' => 'Nominal minimal Rp 1.000.',
            'jumlah_cicilan.required_if' => 'Jumlah cicilan wajib diisi jika memilih cicilan.',
            'due_date.after' => 'Jatuh tempo harus setelah hari ini.',
        ];
    }
}
