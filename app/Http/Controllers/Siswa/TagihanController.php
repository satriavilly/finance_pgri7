<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\TagihanSiswa;
use App\Models\TahunAjaran;
use App\Services\CicilanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function __construct(private CicilanService $cicilanService) {}

    public function index(Request $request): View
    {
        $siswa = auth()->user()->siswa()->with('kelas')->firstOrFail();

        $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        $selectedTa = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $tagihan = TagihanSiswa::with([
                'jenisTagihan.kelas.tahunAjaran',
                'pembayaran' => fn($q) => $q->where('is_void', false)->latest(),
            ])
            ->where('siswa_id', $siswa->id)
            ->where('status', '!=', 'void')
            ->when($selectedTa, fn($q) => $q->whereHas('jenisTagihan.kelas', fn($q2) =>
                $q2->where('tahun_ajaran_id', $selectedTa->id)
            ))
            ->latest()
            ->get();

        return view('siswa.tagihan.index', compact('siswa', 'tagihan', 'allTahunAjaran', 'selectedTa'));
    }

    public function show(int $tagihanId): View
    {
        $tagihan = TagihanSiswa::with(['jenisTagihan', 'siswa', 'cicilan', 'pembayaran.verifiedBy'])->findOrFail($tagihanId);
        $detail = $this->cicilanService->getTagihanDenganCicilan($tagihan);

        return view('siswa.tagihan.show', compact('tagihan', 'detail'));
    }

    public function downloadPdf(): Response
    {
        $siswa = auth()->user()->siswa()->with([
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
