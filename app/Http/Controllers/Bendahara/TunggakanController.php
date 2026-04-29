<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KategoriTagihan;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TunggakanController extends Controller
{
    public function index(Request $request): View
    {
        $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        $selectedTa = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $kelasIds = $selectedTa
            ? Kelas::where('tahun_ajaran_id', $selectedTa->id)->pluck('id')
            : collect();

        $kelasList = $selectedTa
            ? Kelas::where('tahun_ajaran_id', $selectedTa->id)->orderBy('tingkat')->orderBy('nama')->get()
            : collect();

        $kategoriList = KategoriTagihan::orderBy('urutan')->orderBy('nama')->get();

        // Closure to apply all tagihan filters (reused for summary and main query)
        $applyFilters = function ($q) use ($kelasIds, $request) {
            $q->whereIn('status', ['belum_bayar', 'cicilan'])
              ->when($kelasIds->isNotEmpty(), fn($q2) =>
                  $q2->whereHas('jenisTagihan', fn($q3) => $q3->whereIn('kelas_id', $kelasIds))
              )
              ->when($request->filled('kelas_id'), fn($q2) =>
                  $q2->whereHas('jenisTagihan', fn($q3) => $q3->where('kelas_id', $request->kelas_id))
              )
              ->when($request->filled('kategori'), fn($q2) =>
                  $q2->whereHas('jenisTagihan', fn($q3) => $q3->where('kategori', $request->kategori))
              );
        };

        $tagihanBase = TagihanSiswa::where(fn($q) => $applyFilters($q));

        $summary = [
            'siswa_nunggak'      => Siswa::whereHas('tagihanSiswa', fn($q) => $applyFilters($q))->count(),
            'total_tunggakan'    => (clone $tagihanBase)->sum(DB::raw('nominal_total - nominal_terbayar')),
            'jumlah_tagihan'     => (clone $tagihanBase)->count(),
            'jumlah_belum_bayar' => (clone $tagihanBase)->where('status', 'belum_bayar')->count(),
            'jumlah_cicilan'     => (clone $tagihanBase)->where('status', 'cicilan')->count(),
        ];

        $siswa = Siswa::with([
            'tagihanSiswa' => fn($q) => $applyFilters($q)->with(['jenisTagihan.kelas']),
        ])
        ->whereHas('tagihanSiswa', fn($q) => $applyFilters($q))
        ->when($request->filled('cari'), fn($q) => $q->where(fn($sub) => $sub
            ->where('nama', 'ilike', '%'.$request->cari.'%')
            ->orWhere('nis', 'ilike', '%'.$request->cari.'%')
        ))
        ->orderBy('nama')
        ->paginate(30)->withQueryString();

        return view('bendahara.tunggakan.index', compact(
            'allTahunAjaran', 'selectedTa', 'kelasList', 'kategoriList', 'summary', 'siswa'
        ));
    }
}
