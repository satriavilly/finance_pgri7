<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class TagihanController extends Controller
{
    public function downloadPdf(): Response
    {
        $siswa = auth()->user()->anak()->with([
            'kelas.tahunAjaran',
            'tagihanSiswa' => fn($q) => $q->where('status', '!=', 'void')
                ->with([
                    'jenisTagihan.kelas.tahunAjaran',
                    'pembayaran' => fn($q) => $q->where('is_void', false)->latest(),
                ]),
        ])->firstOrFail();

        $pdf = Pdf::loadView('siswa.tagihan.pdf', compact('siswa'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-tagihan-' . $siswa->nis . '.pdf');
    }
}
