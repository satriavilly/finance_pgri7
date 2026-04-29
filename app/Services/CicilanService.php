<?php

namespace App\Services;

use App\Models\Cicilan;
use App\Models\JenisTagihan;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use Illuminate\Support\Facades\DB;

class CicilanService
{
    public function buatTagihanUntukKelas(JenisTagihan $jenisTagihan): void
    {
        DB::transaction(function () use ($jenisTagihan) {
            $siswaDiKelas = Siswa::where('kelas_id', $jenisTagihan->kelas_id)->get();

            foreach ($siswaDiKelas as $siswa) {
                $tagihan = TagihanSiswa::firstOrCreate(
                    ['siswa_id' => $siswa->id, 'jenis_tagihan_id' => $jenisTagihan->id],
                    [
                        'nominal_total' => $jenisTagihan->total_nominal,
                        'nominal_terbayar' => 0,
                        'status' => 'belum_bayar',
                        'due_date' => $jenisTagihan->due_date,
                    ]
                );

                if ($jenisTagihan->is_cicilan && $jenisTagihan->jumlah_cicilan && $tagihan->wasRecentlyCreated) {
                    $this->buatCicilanUntukTagihan($tagihan, $jenisTagihan);
                }
            }
        });
    }

    private function buatCicilanUntukTagihan(TagihanSiswa $tagihan, JenisTagihan $jenisTagihan): void
    {
        $nominalPerCicilan = $jenisTagihan->total_nominal / $jenisTagihan->jumlah_cicilan;

        for ($i = 1; $i <= $jenisTagihan->jumlah_cicilan; $i++) {
            Cicilan::create([
                'tagihan_siswa_id' => $tagihan->id,
                'ke' => $i,
                'nominal' => round($nominalPerCicilan, 2),
                'due_date' => $jenisTagihan->due_date
                    ? $jenisTagihan->due_date->addMonths($i - 1)
                    : null,
                'status' => 'belum_bayar',
            ]);
        }
    }

    public function getTagihanDenganCicilan(TagihanSiswa $tagihan): array
    {
        return [
            'tagihan' => $tagihan->load('jenisTagihan', 'siswa'),
            'cicilan' => $tagihan->cicilan()->with('pembayaran')->orderBy('ke')->get(),
            'pembayaran' => $tagihan->pembayaran()->where('is_void', false)->latest()->get(),
        ];
    }
}
