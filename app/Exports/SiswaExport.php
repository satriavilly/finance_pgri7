<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private int $tahunAjaranId) {}

    public function collection()
    {
        return Siswa::with(['kelas.tahunAjaran'])
            ->whereHas('kelas', fn($q) => $q->where('tahun_ajaran_id', $this->tahunAjaranId))
            ->orderBy('nama')
            ->get()
            ->values()
            ->map(fn($siswa, $i) => [
                $i + 1,
                $siswa->nis,
                $siswa->nama,
                $siswa->jenis_kelamin ?? '-',
                $siswa->kelas?->nama ?? '-',
                $siswa->kelas?->tingkat ?? '-',
                $siswa->kelas?->tahunAjaran?->nama ?? '-',
                $siswa->alamat ?? '',
            ]);
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama', 'Kelamin', 'Kelas', 'Tingkat', 'Tahun Ajaran', 'Alamat'];
    }

    public function title(): string
    {
        return 'Data Siswa';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
