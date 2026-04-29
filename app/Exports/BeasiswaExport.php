<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BeasiswaExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private int $tahunAjaranId) {}

    public function collection()
    {
        $kelasIds = Kelas::where('tahun_ajaran_id', $this->tahunAjaranId)->pluck('id');

        $siswaList = Siswa::with([
                'kelas',
                'tagihanSiswa' => fn($q) => $q
                    ->where('nominal_subsidi', '>', 0)
                    ->where('status', '!=', 'void')
                    ->with([
                        'jenisTagihan',
                        'pembayaran' => fn($p) => $p->where('metode', 'beasiswa')->where('is_void', false),
                    ]),
            ])
            ->whereIn('kelas_id', $kelasIds)
            ->whereHas('tagihanSiswa', fn($q) => $q->where('nominal_subsidi', '>', 0)->where('status', '!=', 'void'))
            ->orderBy('nama')
            ->get();

        $rows = collect();
        $no   = 1;

        foreach ($siswaList as $siswa) {
            foreach ($siswa->tagihanSiswa as $t) {
                $namaBeasiswa = $t->pembayaran->first()?->catatan ?? '-';
                $pct = $t->nominal_total > 0
                    ? round($t->nominal_subsidi / $t->nominal_total * 100) . '%'
                    : '0%';

                $rows->push([
                    $no++,
                    $siswa->nis,
                    $siswa->nama,
                    $siswa->kelas?->nama ?? '-',
                    $t->jenisTagihan?->nama ?? '-',
                    \App\Models\JenisTagihan::kategoriLabel()[$t->jenisTagihan?->kategori ?? ''] ?? '-',
                    number_format($t->nominal_total, 0, ',', '.'),
                    number_format($t->nominal_subsidi, 0, ',', '.'),
                    $pct,
                    $namaBeasiswa,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama', 'Kelas', 'Tagihan', 'Kategori', 'Nominal (Rp)', 'Subsidi (Rp)', '% Subsidi', 'Nama Beasiswa'];
    }

    public function title(): string
    {
        return 'Penerima Beasiswa';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
