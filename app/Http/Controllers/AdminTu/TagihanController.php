<?php

namespace App\Http\Controllers\AdminTu;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminTu\StoreJenisTagihanRequest;
use App\Models\JenisTagihan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Services\CicilanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function __construct(private CicilanService $cicilanService) {}

    public function index(Request $request): View
    {
        $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();

        $tahunAjaran = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $jenisTagihanList = $tahunAjaran
            ? JenisTagihan::with(['kelas' => fn($q) => $q->withCount('siswa'), 'creator'])
                ->withCount('tagihanSiswa')
                ->whereHas('kelas', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
                ->whereNull('deleted_at')
                ->latest()
                ->get()
            : collect();

        return view('admin-tu.tagihan.index', compact('allTahunAjaran', 'tahunAjaran', 'jenisTagihanList'));
    }

    public function create(): View
    {
        $perAngkatan = Kelas::withCount('siswa')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->orderBy('tingkat')->orderBy('nama')
            ->get()
            ->groupBy('tingkat');

        return view('admin-tu.tagihan.create', compact('perAngkatan'));
    }

    public function store(StoreJenisTagihanRequest $request): RedirectResponse
    {
        $data           = $request->validated();
        $isCicilan      = $data['is_cicilan'] ?? false;
        $jumlahCicilan  = $isCicilan ? ($data['jumlah_cicilan'] ?? 1) : 1;
        $kelasIds       = $data['kelas_ids'];
        $jumlahSiswa    = 0;
        $jumlahKelas    = 0;

        foreach ($kelasIds as $kelasId) {
            $jenisTagihan = JenisTagihan::create([
                'kelas_id'       => $kelasId,
                'nama'           => $data['nama'],
                'deskripsi'      => $data['deskripsi'] ?? null,
                'kategori'       => $data['kategori'],
                'total_nominal'  => $data['total_nominal'],
                'due_date'       => $data['due_date'] ?? null,
                'is_cicilan'     => $isCicilan,
                'jumlah_cicilan' => $jumlahCicilan,
                'is_aktif'       => true,
                'created_by'     => auth()->id(),
            ]);

            $before       = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jenisTagihan->id)->count();
            $this->cicilanService->buatTagihanUntukKelas($jenisTagihan);
            $after        = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jenisTagihan->id)->count();
            $jumlahSiswa += $after - $before;
            $jumlahKelas++;
        }

        return redirect()->route('admin-tu.tagihan.index')
            ->with('success', "Tagihan \"{$data['nama']}\" berhasil dibuat untuk {$jumlahKelas} kelas. {$jumlahSiswa} tagihan didistribusikan ke siswa.");
    }

    public function edit(JenisTagihan $tagihan): View
    {
        $kelasList = Kelas::withCount('siswa')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();

        return view('admin-tu.tagihan.edit', compact('tagihan', 'kelasList'));
    }

    public function update(Request $request, JenisTagihan $tagihan): RedirectResponse
    {
        $request->validate([
            'nama'           => ['required', 'string', 'max:255'],
            'deskripsi'      => ['nullable', 'string', 'max:1000'],
            'kategori'       => ['required', 'in:kas_kelas,buku_lks,kegiatan,seragam,lainnya'],
            'total_nominal'  => ['required', 'numeric', 'min:1000'],
            'due_date'       => ['nullable', 'date'],
            'is_cicilan'     => ['boolean'],
            'jumlah_cicilan' => ['required_if:is_cicilan,1', 'nullable', 'integer', 'min:2', 'max:12'],
        ], [
            'total_nominal.min'          => 'Nominal minimal Rp 1.000.',
            'jumlah_cicilan.required_if' => 'Jumlah cicilan wajib diisi jika memilih cicilan.',
        ]);

        $isCicilan = $request->boolean('is_cicilan');

        $tagihan->update([
            'nama'           => $request->nama,
            'deskripsi'      => $request->deskripsi,
            'kategori'       => $request->kategori,
            'total_nominal'  => $request->total_nominal,
            'due_date'       => $request->due_date ?: null,
            'is_cicilan'     => $isCicilan,
            'jumlah_cicilan' => $isCicilan ? $request->jumlah_cicilan : 1,
        ]);

        return redirect()->route('admin-tu.tagihan.index')
            ->with('success', "Tagihan \"{$tagihan->nama}\" berhasil diperbarui.");
    }

    public function distribusiSemua(Request $request): RedirectResponse
    {
        $tahunAjaran = $request->filled('ta')
            ? TahunAjaran::find($request->integer('ta'))
            : TahunAjaran::aktif();

        abort_if(!$tahunAjaran, 404);

        $list = JenisTagihan::whereHas('kelas', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->whereNull('deleted_at')
            ->get();

        $jumlahBaru = 0;
        foreach ($list as $jt) {
            $before = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jt->id)->count();
            $this->cicilanService->buatTagihanUntukKelas($jt);
            $jumlahBaru += \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jt->id)->count() - $before;
        }

        $pesan = $jumlahBaru > 0
            ? "{$jumlahBaru} tagihan baru berhasil didistribusikan ke siswa yang belum memilikinya."
            : 'Semua siswa sudah memiliki semua tagihan di tahun ajaran ini.';

        return redirect()->route('admin-tu.tagihan.index', ['ta' => $tahunAjaran->id])
            ->with('success', $pesan);
    }

    public function distribusiUlang(JenisTagihan $tagihan): RedirectResponse
    {
        $sebelum = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $tagihan->id)->count();
        $this->cicilanService->buatTagihanUntukKelas($tagihan);
        $sesudah = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $tagihan->id)->count();
        $baru    = $sesudah - $sebelum;

        $pesan = $baru > 0
            ? "{$baru} tagihan baru berhasil didistribusikan ke siswa yang belum memilikinya."
            : 'Semua siswa sudah memiliki tagihan ini.';

        return redirect()->route('admin-tu.tagihan.index')->with('success', $pesan);
    }

    public function penerima(JenisTagihan $tagihan): View
    {
        $tagihan->load('kelas');

        $sudahIds = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $tagihan->id)
            ->pluck('siswa_id');

        $belum = \App\Models\Siswa::where('kelas_id', $tagihan->kelas_id)
            ->whereNotIn('id', $sudahIds)
            ->orderBy('nama')
            ->get();

        $sudah = \App\Models\Siswa::where('kelas_id', $tagihan->kelas_id)
            ->whereIn('id', $sudahIds)
            ->orderBy('nama')
            ->get();

        return view('admin-tu.tagihan.penerima', compact('tagihan', 'belum', 'sudah'));
    }

    public function destroy(JenisTagihan $tagihan): RedirectResponse
    {
        $tagihan->delete();

        return redirect()->route('admin-tu.tagihan.index')
            ->with('success', "Tagihan \"{$tagihan->nama}\" berhasil dihapus.");
    }
}
