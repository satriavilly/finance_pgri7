<?php

namespace App\Exports;

use App\Models\JenisBeasiswa;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BeasiswaTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new BeasiswaTemplateDataSheet(),
            new BeasiswaTemplateKodeSheet(),
        ];
    }
}

class BeasiswaTemplateDataSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            ['nis', 'kode_beasiswa'],
            ['123456', 'kip'],         // contoh — sesuaikan kode dari sheet "Referensi Kode"
            ['234567', 'beasiswa_prestasi'],
        ];
    }

    public function title(): string { return 'Data Import'; }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 28];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:B3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
        ]);

        $sheet->setCellValue('A5', 'Petunjuk:');
        $sheet->setCellValue('A6', '1. Isi kolom nis dengan NIS siswa (wajib).');
        $sheet->setCellValue('A7', '2. Isi kolom kode_beasiswa dengan kode dari sheet "Referensi Kode" (wajib).');
        $sheet->setCellValue('A8', '3. Hapus baris contoh (baris 2-3) sebelum upload.');
        $sheet->setCellValue('A9', '4. Satu baris = satu siswa. Semua tagihan belum lunas akan dilunasi.');
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('A5:A9')->getFont()->setSize(9);
        $sheet->getStyle('A6:A9')->getFont()->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color('6B7280'))
        );

        return [];
    }
}

class BeasiswaTemplateKodeSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        $rows = [['kode', 'nama', 'sumber', 'deskripsi']];

        $jenisList = JenisBeasiswa::where('is_aktif', true)->orderBy('nama')->get();

        if ($jenisList->isEmpty()) {
            $rows[] = ['(belum ada)', 'Tambahkan dulu di menu Master Beasiswa', '', ''];
        } else {
            foreach ($jenisList as $j) {
                $rows[] = [$j->kode, $j->nama, $j->sumber ?? '-', $j->deskripsi ?? '-'];
            }
        }

        return $rows;
    }

    public function title(): string { return 'Referensi Kode'; }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 35, 'C' => 25, 'D' => 45];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B21B6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }
}
