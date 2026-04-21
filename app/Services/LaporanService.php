<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\TagihanSiswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    public function rekapPembayaranKelas(int $kelasId, int $tahunAjaranId): Collection
    {
        return TagihanSiswa::with(['siswa', 'jenisTagihan', 'pembayaran'])
            ->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId))
            ->whereHas('jenisTagihan', fn($q) => $q->where('kelas_id', $kelasId))
            ->get()
            ->groupBy('siswa_id');
    }

    public function tunggakanKelas(int $kelasId): Collection
    {
        return TagihanSiswa::with(['siswa', 'jenisTagihan'])
            ->whereIn('status', ['belum_bayar', 'cicilan'])
            ->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId))
            ->get();
    }

    public function dashboardBendahara(int $tahunAjaranId): array
    {
        $kelasIds = Kelas::where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');

        $totalTagihan = TagihanSiswa::whereHas('jenisTagihan', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->where('status', '!=', 'void')->sum('nominal_total');

        $totalTerbayar = TagihanSiswa::whereHas('jenisTagihan', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->where('status', '!=', 'void')->sum('nominal_terbayar');

        $totalTunggakan = $totalTagihan - $totalTerbayar;

        $pemasukanBulanIni = Pembayaran::where('status_verifikasi', 'approved')
            ->where('is_void', false)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        $menungguVerifikasi = Pembayaran::where('status_verifikasi', 'pending')
            ->where('is_void', false)->count();

        $perKelas = Kelas::where('tahun_ajaran_id', $tahunAjaranId)
            ->withCount('siswa')
            ->orderBy('tingkat')->orderBy('nama')
            ->get()
            ->map(function ($kelas) {
                $total     = TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                    ->where('status', '!=', 'void')->sum('nominal_total');
                $terbayar  = TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                    ->where('status', '!=', 'void')->sum('nominal_terbayar');
                $lunasCount = \App\Models\Siswa::where('kelas_id', $kelas->id)
                    ->whereDoesntHave('tagihanSiswa', fn($q) => $q->whereIn('status', ['belum_bayar', 'cicilan']))
                    ->count();
                $pct = $total > 0 ? min(100, round($terbayar / $total * 100)) : 0;
                return [
                    'kelas'         => $kelas,
                    'total'         => $total,
                    'terbayar'      => $terbayar,
                    'tunggakan'     => $total - $terbayar,
                    'pct'           => $pct,
                    'lunas_count'   => $lunasCount,
                    'tunggakan_count' => $kelas->siswa_count - $lunasCount,
                ];
            });

        return compact('totalTagihan', 'totalTerbayar', 'totalTunggakan', 'pemasukanBulanIni', 'menungguVerifikasi', 'perKelas');
    }

    public function dashboardWaliKelas(int $kelasId): array
    {
        $totalSiswa = \App\Models\Siswa::where('kelas_id', $kelasId)->count();

        $sudahLunasSemua = \App\Models\Siswa::where('kelas_id', $kelasId)
            ->whereDoesntHave('tagihanSiswa', fn($q) => $q->whereIn('status', ['belum_bayar', 'cicilan']))
            ->count();

        $menungguVerifikasi = Pembayaran::where('status_verifikasi', 'pending')
            ->where('is_void', false)
            ->whereHas('tagihanSiswa.siswa', fn($q) => $q->where('kelas_id', $kelasId))
            ->count();

        $totalTagihan = TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('status', '!=', 'void')
            ->sum('nominal_total');

        $totalTerbayar = TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('status', '!=', 'void')
            ->sum('nominal_terbayar');

        return compact('totalSiswa', 'sudahLunasSemua', 'menungguVerifikasi', 'totalTagihan', 'totalTerbayar');
    }

    public function rekapBulanan(int $kelasId, int $tahun): array
    {
        $data = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $data[$bulan] = Pembayaran::where('status_verifikasi', 'approved')
                ->where('is_void', false)
                ->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->whereHas('tagihanSiswa.siswa', fn($q) => $q->where('kelas_id', $kelasId))
                ->sum('nominal');
        }
        return $data;
    }
}
