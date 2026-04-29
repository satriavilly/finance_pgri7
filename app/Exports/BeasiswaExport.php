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

        return Siswa::with(['kelas', 'tagihanSiswa' => fn($q) => $q
                ->where('nominal_subsidi', '>', 0)
                ->where('status', '!=', 'void')
                ->with('jenisTagihan'),
            ])
            ->whereIn('kelas_id', $kelasIds)
            ->whereHas('tagihanSiswa', fn($q) => $q->where('nominal_subsidi', '>', 0)->where('status', '!=', 'void'))
            ->orderBy('nama')
            ->get()
            ->values()
            ->map(fn($siswa, $i) => [
                $i + 1,
                $siswa->nis,
                $siswa->nama,
                $siswa->kelas?->nama ?? '-',
                $siswa->tagihanSiswa->count(),
                number_format($siswa->tagihanSiswa->sum('nominal_subsidi'), 0, ',', '.'),
            ]);
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama', 'Kelas', 'Jumlah Tagihan', 'Total Subsidi (Rp)'];
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
