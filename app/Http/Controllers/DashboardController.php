<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private LaporanService $laporanService) {}

    public function index(): View
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
            $siswa = $user->siswa()->with([
                'kelas',
                'tagihanSiswa' => fn($q) => $q->where('status', '!=', 'void')
                    ->with(['jenisTagihan', 'pembayaran' => fn($q) => $q->where('is_void', false)->latest()]),
            ])->first();
            return view('dashboard.siswa', compact('siswa'));
        }

        if ($user->hasRole('ortu')) {
            $anak = $user->anak()->with('kelas', 'tagihanSiswa.jenisTagihan')->first();
            return view('dashboard.ortu', compact('anak'));
        }

        return view('dashboard.default');
    }
}
