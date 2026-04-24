<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private LaporanService $laporanService) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $tahunAjaran = TahunAjaran::aktif();

        if ($user->hasRole('admin')) {
            return view('dashboard.admin', compact('tahunAjaran'));
        }

        if ($user->hasRole('bendahara')) {
            $data = $tahunAjaran ? $this->laporanService->dashboardBendahara($tahunAjaran->id) : [];
            return view('dashboard.bendahara', compact('tahunAjaran', 'data'));
        }

        if ($user->hasRole('admin_tu')) {
            $totalTagihan = \App\Models\JenisTagihan::whereHas('kelas.tahunAjaran', fn($q) => $q->where('is_aktif', true))
                ->whereNull('deleted_at')
                ->count();
            $totalKelas = \App\Models\Kelas::whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))->count();
            return view('dashboard.admin_tu', compact('tahunAjaran', 'totalTagihan', 'totalKelas'));
        }

        if ($user->hasRole('wali_kelas')) {
            $kelas = $user->kelasWali()->where('tahun_ajaran_id', $tahunAjaran?->id)->first();
            $data = $kelas ? $this->laporanService->dashboardWaliKelas($kelas->id) : [];
            return view('dashboard.wali_kelas', compact('tahunAjaran', 'kelas', 'data'));
        }

        if ($user->hasRole('kepsek')) {
            $data = $tahunAjaran ? $this->laporanService->dashboardKepsek($tahunAjaran->id) : [];
            return view('dashboard.kepsek', compact('tahunAjaran', 'data'));
        }

        if ($user->hasRole('siswa')) {
            $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
            $selectedTa = $request->filled('ta')
                ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
                : ($tahunAjaran ?? $allTahunAjaran->first());

            $siswa = $user->siswa()->with([
                'kelas',
                'tagihanSiswa' => fn($q) => $q->where('status', '!=', 'void')
                    ->when($selectedTa, fn($q) => $q->whereHas('jenisTagihan.kelas', fn($q2) =>
                        $q2->where('tahun_ajaran_id', $selectedTa->id)
                    ))
                    ->with([
                        'jenisTagihan',
                        'pembayaran' => fn($q) => $q->where('is_void', false)->latest(),
                    ]),
            ])->first();

            return view('dashboard.siswa', compact('siswa', 'tahunAjaran', 'allTahunAjaran', 'selectedTa'));
        }

        if ($user->hasRole('ortu')) {
            $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
            $selectedTa = $request->filled('ta')
                ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
                : ($tahunAjaran ?? $allTahunAjaran->first());

            $anak = $user->anak()->with([
                'kelas',
                'tagihanSiswa' => fn($q) => $q->where('status', '!=', 'void')
                    ->when($selectedTa, fn($q) => $q->whereHas('jenisTagihan.kelas', fn($q2) =>
                        $q2->where('tahun_ajaran_id', $selectedTa->id)
                    ))
                    ->with([
                        'jenisTagihan',
                        'pembayaran' => fn($q) => $q->where('is_void', false)->latest(),
                    ]),
            ])->first();

            return view('dashboard.ortu', compact('anak', 'tahunAjaran', 'allTahunAjaran', 'selectedTa'));
        }

        return view('dashboard.default');
    }
}
