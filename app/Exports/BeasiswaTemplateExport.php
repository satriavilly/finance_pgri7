<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BeasiswaTemplateExport implements FromArray, WithStyles, WithTitle, WithColumnWidths
{
    public function array(): array
    {
        return [
            ['nis', 'nama_beasiswa'],
            ['123456', 'Beasiswa Prestasi'],
            ['234567', 'KIP (Kartu Indonesia Pintar)'],
            ['345678', 'Beasiswa Sekolah'],
        ];
    }

    public function title(): string
    {
        return 'Template Beasiswa';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 40,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row bold + background
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Sample rows italic + light bg
        $sheet->getStyle('A2:B4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
        ]);

        // Add a note in row 6
        $sheet->setCellValue('A6', 'Petunjuk:');
        $sheet->setCellValue('A7', '- Kolom nis: wajib diisi, harus sesuai NIS siswa di sistem.');
        $sheet->setCellValue('A7', '- Kolom nis: wajib diisi, harus sesuai NIS siswa di sistem.');
        $sheet->setCellValue('A8', '- Kolom nama_beasiswa: opsional. Jika kosong, diisi "Beasiswa / Subsidi Penuh".');
        $sheet->setCellValue('A9', '- Hapus baris contoh (baris 2-4) sebelum mengisi data asli.');
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->getStyle('A6:B9')->getFont()->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color('9CA3AF'))
        );

        return [];
    }
}
