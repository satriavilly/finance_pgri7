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
            'nominal' => [
                'required',
                'numeric',
                'min:1000',
                function ($attr, $value, $fail) {
                    $tagihan = \App\Models\TagihanSiswa::find($this->route('tagihan'));
                    if ($tagihan && $value > $tagihan->sisa_tagihan) {
                        $fail('Nominal melebihi sisa tagihan (Rp ' . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ').');
                    }
                },
            ],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'cicilan_id'    => ['nullable', 'exists:cicilan,id'],
            'catatan'       => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nominal.required'             => 'Nominal pembayaran wajib diisi.',
            'nominal.min'                  => 'Nominal minimal Rp 1.000.',
            'tanggal_bayar.required'       => 'Tanggal bayar wajib diisi.',
            'tanggal_bayar.before_or_equal'=> 'Tanggal bayar tidak boleh lebih dari hari ini.',
        ];
    }
}
