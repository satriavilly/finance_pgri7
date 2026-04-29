<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Services\PembayaranService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BeasiswaImport implements ToCollection, WithHeadingRow
{
    public int $applied = 0;
    public array $errors = [];

    public function __construct(
        private int $tahunAjaranId,
        private int $userId,
        private PembayaranService $pembayaranService,
    ) {}

    public function collection(Collection $rows): void
    {
        $kelasIds = Kelas::where('tahun_ajaran_id', $this->tahunAjaranId)->pluck('id');

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            $nis          = trim((string) ($row['nis'] ?? ''));
            $namaBeasiswa = trim((string) ($row['nama_beasiswa'] ?? ''));
            if ($namaBeasiswa === '') {
                $namaBeasiswa = 'Beasiswa / Subsidi Penuh';
            }

            if ($nis === '') {
                continue;
            }

            $siswa = Siswa::where('nis', $nis)->whereIn('kelas_id', $kelasIds)->first();

            if (!$siswa) {
                $this->errors[] = "Baris {$rowNum}: NIS \"{$nis}\" tidak ditemukan di tahun ajaran ini.";
                continue;
            }

            $tagihans = TagihanSiswa::where('siswa_id', $siswa->id)
                ->whereIn('status', ['belum_bayar', 'cicilan'])
                ->whereHas('jenisTagihan', fn($q) => $q->whereIn('kelas_id', $kelasIds))
                ->get();

            if ($tagihans->isEmpty()) {
                $this->errors[] = "Baris {$rowNum}: {$siswa->nama} tidak memiliki tagihan belum lunas.";
                continue;
            }

            foreach ($tagihans as $tagihan) {
                try {
                    $this->pembayaranService->terapkanBeasiswaSiswa($tagihan, $this->userId, $namaBeasiswa);
                } catch (\Throwable $e) {
                    $this->errors[] = "Baris {$rowNum}: {$siswa->nama} — {$e->getMessage()}";
                    continue 2;
                }
            }

            $this->applied++;
        }
    }
}
