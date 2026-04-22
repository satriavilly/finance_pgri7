<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Http\Requests\WaliKelas\StoreJenisTagihanRequest;
use App\Models\JenisTagihan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Services\CicilanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function __construct(private CicilanService $cicilanService) {}

    public function index(): View
    {
        $kelas = $this->getKelasWaliKelas();
        $jenisTagihan = JenisTagihan::where('kelas_id', $kelas->id)
            ->with('creator')
            ->latest()
            ->paginate(15);

        return view('wali-kelas.tagihan.index', compact('kelas', 'jenisTagihan'));
    }

    public function create(): View
    {
        $kelas = $this->getKelasWaliKelas();
        return view('wali-kelas.tagihan.create', compact('kelas'));
    }

    public function store(StoreJenisTagihanRequest $request): RedirectResponse
    {
        $kelas = $this->getKelasWaliKelas();
        $jenisTagihan = JenisTagihan::create([
            ...$request->validated(),
            'kelas_id' => $kelas->id,
            'created_by' => auth()->id(),
        ]);

        $this->cicilanService->buatTagihanUntukKelas($jenisTagihan);

        return redirect()->route('wali-kelas.tagihan.index')
            ->with('success', 'Tagihan berhasil dibuat dan sudah didistribusikan ke semua siswa.');
    }

    public function distribusiUlang(): RedirectResponse
    {
        $kelas = $this->getKelasWaliKelas();

        $jenisTagihanList = JenisTagihan::where('kelas_id', $kelas->id)
            ->where('is_aktif', true)
            ->whereNull('deleted_at')
            ->get();

        $jumlahTagihanBaru = 0;
        foreach ($jenisTagihanList as $jenisTagihan) {
            $sebelum = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jenisTagihan->id)->count();
            $this->cicilanService->buatTagihanUntukKelas($jenisTagihan);
            $sesudah = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jenisTagihan->id)->count();
            $jumlahTagihanBaru += ($sesudah - $sebelum);
        }

        $pesan = $jumlahTagihanBaru > 0
            ? "{$jumlahTagihanBaru} tagihan baru berhasil didistribusikan ke siswa yang belum memiliki tagihan."
            : 'Semua siswa sudah memiliki tagihan. Tidak ada tagihan baru yang perlu dibuat.';

        return redirect()->route('wali-kelas.tagihan.index')->with('success', $pesan);
    }

    private function getKelasWaliKelas(): Kelas
    {
        $tahunAjaran = TahunAjaran::aktif();
        return auth()->user()->kelasWali()
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->firstOrFail();
    }
}
