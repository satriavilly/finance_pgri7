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
        $this->authorize('viewAny', JenisTagihan::class);

        $kelas = $this->getKelasWaliKelas();
        $jenisTagihan = JenisTagihan::where('kelas_id', $kelas->id)
            ->with('creator')
            ->latest()
            ->paginate(15);

        return view('wali-kelas.tagihan.index', compact('kelas', 'jenisTagihan'));
    }

    public function create(): View
    {
        $this->authorize('create', JenisTagihan::class);
        $kelas = $this->getKelasWaliKelas();
        return view('wali-kelas.tagihan.create', compact('kelas'));
    }

    public function store(StoreJenisTagihanRequest $request): RedirectResponse
    {
        $this->authorize('create', JenisTagihan::class);

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

    private function getKelasWaliKelas(): Kelas
    {
        $tahunAjaran = TahunAjaran::aktif();
        return auth()->user()->kelasWali()
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->firstOrFail();
    }
}
