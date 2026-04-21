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
                $total      = TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                    ->where('status', '!=', 'void')->sum('nominal_total');
                $terbayar   = TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                    ->where('status', '!=', 'void')->sum('nominal_terbayar');
                $lunasCount = \App\Models\Siswa::where('kelas_id', $kelas->id)
                    ->whereDoesntHave('tagihanSiswa', fn($q) => $q->whereIn('status', ['belum_bayar', 'cicilan']))->count();
                $pct = $total > 0 ? min(100, round($terbayar / $total * 100)) : 0;
                return [
                    'kelas'           => $kelas,
                    'total'           => $total,
                    'terbayar'        => $terbayar,
                    'tunggakan'       => $total - $terbayar,
                    'pct'             => $pct,
                    'lunas_count'     => $lunasCount,
                    'tunggakan_count' => $kelas->siswa_count - $lunasCount,
                ];
            });

        // Trend bulanan tahun ini
        $tahunIni = now()->year;
        $bulanLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $pemasukanBulanan = [];
        for ($b = 1; $b <= 12; $b++) {
            $pemasukanBulanan[] = (float) Pembayaran::where('status_verifikasi', 'approved')
                ->where('is_void', false)->whereMonth('created_at', $b)->whereYear('created_at', $tahunIni)
                ->whereHas('tagihanSiswa.siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))
                ->sum('nominal');
        }

        // Metode pembayaran
        $metodeData = Pembayaran::where('status_verifikasi', 'approved')->where('is_void', false)
            ->whereHas('tagihanSiswa.siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->select('metode', DB::raw('sum(nominal) as total'), DB::raw('count(*) as jumlah'))
            ->groupBy('metode')->get()->keyBy('metode');

        // Status distribusi tagihan
        $statusDist = TagihanSiswa::whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->where('status', '!=', 'void')
            ->select('status', DB::raw('count(*) as jumlah'))
            ->groupBy('status')->pluck('jumlah', 'status');

        // Pembayaran terbaru (8 terakhir)
        $pembayaranTerbaru = Pembayaran::with(['tagihanSiswa.siswa', 'tagihanSiswa.jenisTagihan'])
            ->where('status_verifikasi', 'approved')->where('is_void', false)
            ->whereHas('tagihanSiswa.siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->latest()->limit(8)->get();

        return compact(
            'totalTagihan', 'totalTerbayar', 'totalTunggakan', 'pemasukanBulanIni', 'menungguVerifikasi',
            'perKelas', 'bulanLabels', 'pemasukanBulanan', 'metodeData', 'statusDist', 'pembayaranTerbaru', 'tahunIni'
        );
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

    public function dashboardKepsek(int $tahunAjaranId): array
    {
        $kelasIds = Kelas::where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');

        // KPI global
        $totalTagihan  = TagihanSiswa::whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))->where('status','!=','void')->sum('nominal_total');
        $totalTerbayar = TagihanSiswa::whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))->where('status','!=','void')->sum('nominal_terbayar');
        $totalSiswa    = \App\Models\Siswa::whereIn('kelas_id', $kelasIds)->count();
        $siswaLunas    = \App\Models\Siswa::whereIn('kelas_id', $kelasIds)
            ->whereDoesntHave('tagihanSiswa', fn($q) => $q->whereIn('status',['belum_bayar','cicilan']))->count();

        // Donut: distribusi status tagihan
        $statusDist = TagihanSiswa::whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->where('status','!=','void')
            ->select('status', DB::raw('count(*) as jumlah'))
            ->groupBy('status')->pluck('jumlah','status');

        // Bar per kelas
        $perKelas = Kelas::where('tahun_ajaran_id', $tahunAjaranId)
            ->withCount('siswa')->orderBy('tingkat')->orderBy('nama')->get()
            ->map(fn($k) => [
                'nama'      => $k->nama,
                'terbayar'  => TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id',$k->id))->where('status','!=','void')->sum('nominal_terbayar'),
                'tunggakan' => TagihanSiswa::whereHas('siswa', fn($q) => $q->where('kelas_id',$k->id))->where('status','!=','void')->sum(DB::raw('nominal_total - nominal_terbayar')),
                'siswa'     => $k->siswa_count,
                'lunas'     => \App\Models\Siswa::where('kelas_id',$k->id)->whereDoesntHave('tagihanSiswa', fn($q) => $q->whereIn('status',['belum_bayar','cicilan']))->count(),
            ]);

        // Line: pemasukan per bulan tahun ini
        $tahunIni = now()->year;
        $bulanLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $pemasukanBulanan = [];
        for ($b = 1; $b <= 12; $b++) {
            $pemasukanBulanan[] = (float) Pembayaran::where('status_verifikasi','approved')
                ->where('is_void', false)->whereMonth('created_at',$b)->whereYear('created_at',$tahunIni)
                ->whereHas('tagihanSiswa.siswa', fn($q) => $q->whereIn('kelas_id',$kelasIds))
                ->sum('nominal');
        }

        // Pie: per kategori tagihan
        $perKategori = TagihanSiswa::whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->where('status','!=','void')
            ->join('jenis_tagihan','jenis_tagihan.id','=','tagihan_siswa.jenis_tagihan_id')
            ->select('jenis_tagihan.kategori', DB::raw('sum(tagihan_siswa.nominal_total) as total'))
            ->groupBy('jenis_tagihan.kategori')->pluck('total','kategori');

        // Top 7 tunggakan siswa (filter di PHP karena PostgreSQL tidak support HAVING alias)
        $topTunggakan = \App\Models\Siswa::whereIn('kelas_id', $kelasIds)
            ->with('kelas')
            ->withSum(['tagihanSiswa as tunggakan' => fn($q) => $q->whereIn('status', ['belum_bayar', 'cicilan'])], DB::raw('nominal_total - nominal_terbayar'))
            ->get()
            ->filter(fn($s) => ($s->tunggakan ?? 0) > 0)
            ->sortByDesc('tunggakan')
            ->take(7)
            ->values();

        // Metode pembayaran
        $metodeData = Pembayaran::where('status_verifikasi','approved')->where('is_void',false)
            ->whereHas('tagihanSiswa.siswa', fn($q) => $q->whereIn('kelas_id',$kelasIds))
            ->select('metode', DB::raw('sum(nominal) as total'))
            ->groupBy('metode')->pluck('total','metode');

        return compact(
            'totalTagihan','totalTerbayar','totalSiswa','siswaLunas',
            'statusDist','perKelas','bulanLabels','pemasukanBulanan',
            'perKategori','topTunggakan','metodeData','tahunIni'
        );
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
