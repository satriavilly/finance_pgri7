<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\TagihanSiswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function transaksi(Request $request): View
    {
        $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        $tahunAjaran = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $kelasList = $tahunAjaran
            ? Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->orderBy('tingkat')->orderBy('nama')->get()
            : collect();

        $query = Pembayaran::with(['tagihanSiswa.siswa.kelas', 'tagihanSiswa.jenisTagihan'])
            ->where('is_void', false);

        if ($tahunAjaran) {
            $query->whereHas('tagihanSiswa.siswa.kelas', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id));
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('tagihanSiswa.siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }

        if ($request->filled('metode')) {
            $query->where('metode', $request->metode);
        }

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        if ($request->filled('cari')) {
            $query->whereHas('tagihanSiswa.siswa', fn($q) =>
                $q->where('nama', 'ilike', '%'.$request->cari.'%')
                  ->orWhere('nis', 'ilike', '%'.$request->cari.'%')
            );
        }

        $transaksi = $query->latest()->paginate(20)->withQueryString();

        $summary = [
            'total_nominal' => (clone $query->getQuery())->sum('nominal'),
        ];

        // summary tanpa paginate
        $baseQuery = Pembayaran::where('is_void', false);
        if ($tahunAjaran)                          $baseQuery->whereHas('tagihanSiswa.siswa.kelas', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id));
        if ($request->filled('kelas_id'))          $baseQuery->whereHas('tagihanSiswa.siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        if ($request->filled('metode'))             $baseQuery->where('metode', $request->metode);
        if ($request->filled('status_verifikasi')) $baseQuery->where('status_verifikasi', $request->status_verifikasi);
        if ($request->filled('dari'))              $baseQuery->whereDate('created_at', '>=', $request->dari);
        if ($request->filled('sampai'))            $baseQuery->whereDate('created_at', '<=', $request->sampai);
        if ($request->filled('cari'))              $baseQuery->whereHas('tagihanSiswa.siswa', fn($q) => $q->where('nama','ilike','%'.$request->cari.'%')->orWhere('nis','ilike','%'.$request->cari.'%'));

        $summary = [
            'total'    => $baseQuery->sum('nominal'),
            'approved' => (clone $baseQuery)->where('status_verifikasi', 'approved')->sum('nominal'),
            'pending'  => (clone $baseQuery)->where('status_verifikasi', 'pending')->count(),
            'count'    => $baseQuery->count(),
        ];

        return view('bendahara.laporan.transaksi', compact('transaksi', 'kelasList', 'summary', 'allTahunAjaran', 'tahunAjaran'));
    }

    public function tagihan(Request $request): View
    {
        $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        $tahunAjaran = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $kelasList = $tahunAjaran
            ? Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->orderBy('tingkat')->orderBy('nama')->get()
            : collect();

        $query = TagihanSiswa::with(['siswa.kelas', 'jenisTagihan', 'pembayaran'])
            ->where('status', '!=', 'void')
            ->when($tahunAjaran, fn($q) => $q->whereHas('siswa.kelas', fn($q2) => $q2->where('tahun_ajaran_id', $tahunAjaran->id)));

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori')) {
            $query->whereHas('jenisTagihan', fn($q) => $q->where('kategori', $request->kategori));
        }

        if ($request->filled('cari')) {
            $query->whereHas('siswa', fn($q) =>
                $q->where('nama', 'ilike', '%'.$request->cari.'%')
                  ->orWhere('nis',  'ilike', '%'.$request->cari.'%')
            );
        }

        $tagihan = $query->orderBy(
            \App\Models\Siswa::select('nama')->whereColumn('siswa.id', 'tagihan_siswa.siswa_id')
        )->paginate(25)->withQueryString();

        $summary = [
            'total'     => (clone $query->getQuery())->sum('nominal_total'),
            'terbayar'  => (clone $query->getQuery())->sum('nominal_terbayar'),
            'belum'     => (clone $query->getQuery())->whereIn('status', ['belum_bayar','cicilan'])->count(),
            'lunas'     => (clone $query->getQuery())->where('status', 'lunas')->count(),
        ];

        // recalc summary properly
        $sBase = TagihanSiswa::where('status','!=','void')
            ->when($tahunAjaran, fn($q) => $q->whereHas('siswa.kelas', fn($q2) => $q2->where('tahun_ajaran_id', $tahunAjaran->id)));
        if ($request->filled('kelas_id')) $sBase->whereHas('siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        if ($request->filled('status'))   $sBase->where('status', $request->status);
        if ($request->filled('kategori')) $sBase->whereHas('jenisTagihan', fn($q) => $q->where('kategori', $request->kategori));
        if ($request->filled('cari'))     $sBase->whereHas('siswa', fn($q) => $q->where('nama','ilike','%'.$request->cari.'%')->orWhere('nis','ilike','%'.$request->cari.'%'));

        $summary = [
            'total'    => $sBase->sum('nominal_total'),
            'terbayar' => $sBase->sum('nominal_terbayar'),
            'tunggakan'=> $sBase->sum(\Illuminate\Support\Facades\DB::raw('nominal_total - nominal_terbayar')),
            'lunas'    => (clone $sBase)->where('status','lunas')->count(),
            'belum'    => (clone $sBase)->whereIn('status',['belum_bayar','cicilan'])->count(),
        ];

        return view('bendahara.laporan.tagihan', compact('tagihan', 'kelasList', 'summary', 'allTahunAjaran', 'tahunAjaran'));
    }
}
