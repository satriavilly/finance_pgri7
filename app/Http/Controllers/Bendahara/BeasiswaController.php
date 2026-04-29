<?php

namespace App\Http\Controllers\Bendahara;

use App\Exports\BeasiswaExport;
use App\Exports\BeasiswaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\BeasiswaImport;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Models\TahunAjaran;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BeasiswaController extends Controller
{
    public function __construct(private PembayaranService $pembayaranService) {}

    public function index(Request $request): View
    {
        $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        $selectedTa = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $kelasIds = $selectedTa ? Kelas::where('tahun_ajaran_id', $selectedTa->id)->pluck('id') : collect();

        $penerima = Siswa::with([
                'kelas',
                'tagihanSiswa' => fn($q) => $q
                    ->where('nominal_subsidi', '>', 0)
                    ->where('status', '!=', 'void')
                    ->with([
                        'jenisTagihan',
                        'pembayaran' => fn($p) => $p
                            ->where('metode', 'beasiswa')
                            ->where('is_void', false),
                    ]),
            ])
            ->whereIn('kelas_id', $kelasIds)
            ->whereHas('tagihanSiswa', fn($q) => $q->where('nominal_subsidi', '>', 0)->where('status', '!=', 'void'))
            ->when($request->filled('kelas_id'), fn($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->filled('cari'), fn($q) => $q->where(fn($sub) => $sub
                ->where('nama', 'ilike', '%'.$request->cari.'%')
                ->orWhere('nis', 'ilike', '%'.$request->cari.'%')
            ))
            ->orderBy('nama')
            ->paginate(20)->withQueryString();

        $kelasList = $selectedTa
            ? Kelas::where('tahun_ajaran_id', $selectedTa->id)->orderBy('tingkat')->orderBy('nama')->get()
            : collect();

        $siswaBelum = Siswa::with('kelas')
            ->whereIn('kelas_id', $kelasIds)
            ->whereHas('tagihanSiswa', fn($q) => $q->whereIn('status', ['belum_bayar', 'cicilan']))
            ->whereDoesntHave('tagihanSiswa', fn($q) => $q->where('nominal_subsidi', '>', 0)->where('status', '!=', 'void'))
            ->orderBy('nama')
            ->get();

        return view('bendahara.beasiswa.index', compact(
            'allTahunAjaran', 'selectedTa', 'penerima', 'kelasList', 'siswaBelum'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'siswa_id'        => ['required', 'exists:siswa,id'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama_beasiswa'   => ['nullable', 'string', 'max:200'],
        ]);

        $siswaId       = $request->integer('siswa_id');
        $tahunAjaranId = $request->integer('tahun_ajaran_id');
        $namaBeasiswa  = trim($request->input('nama_beasiswa', '')) ?: 'Beasiswa / Subsidi Penuh';

        $kelasIds = Kelas::where('tahun_ajaran_id', $tahunAjaranId)->pluck('id');

        $tagihans = TagihanSiswa::where('siswa_id', $siswaId)
            ->whereIn('status', ['belum_bayar', 'cicilan'])
            ->whereHas('jenisTagihan', fn($q) => $q->whereIn('kelas_id', $kelasIds))
            ->get();

        if ($tagihans->isEmpty()) {
            return back()->with('error', 'Tidak ada tagihan belum lunas untuk siswa ini di tahun ajaran tersebut.');
        }

        foreach ($tagihans as $tagihan) {
            $this->pembayaranService->terapkanBeasiswaSiswa($tagihan, auth()->id(), $namaBeasiswa);
        }

        $siswa = Siswa::find($siswaId);
        return back()->with('success', "Beasiswa diterapkan untuk {$siswa->nama} ({$tagihans->count()} tagihan dilunasi).");
    }

    public function void(Request $request, int $siswaId): RedirectResponse
    {
        $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'catatan_void'    => ['required', 'string', 'max:500'],
        ]);

        $kelasIds = Kelas::where('tahun_ajaran_id', $request->integer('tahun_ajaran_id'))->pluck('id');

        $pembayarans = Pembayaran::where('metode', 'beasiswa')
            ->where('is_void', false)
            ->whereHas('tagihanSiswa', fn($q) => $q
                ->where('siswa_id', $siswaId)
                ->whereHas('jenisTagihan', fn($q2) => $q2->whereIn('kelas_id', $kelasIds))
            )
            ->get();

        foreach ($pembayarans as $p) {
            $p->load('tagihanSiswa');
            $this->pembayaranService->voidPembayaran($p, $request->catatan_void);
        }

        $siswa = Siswa::find($siswaId);
        return back()->with('success', "Beasiswa {$siswa?->nama} berhasil dibatalkan ({$pembayarans->count()} pembayaran di-void).");
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file'            => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
        ]);

        $import = new BeasiswaImport($request->integer('tahun_ajaran_id'), auth()->id(), $this->pembayaranService);
        Excel::import($import, $request->file('file'));

        $msg = "Berhasil: {$import->applied} siswa. Gagal/skip: ".count($import->errors)." baris.";
        if ($import->errors) {
            $msg .= ' Catatan: ' . implode(' | ', array_slice($import->errors, 0, 3));
        }

        return back()->with('success', $msg);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $tahunAjaranId = $request->filled('ta') ? $request->integer('ta') : (TahunAjaran::aktif()?->id ?? 0);
        return Excel::download(new BeasiswaExport($tahunAjaranId), 'beasiswa.xlsx');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new BeasiswaTemplateExport(), 'template-import-beasiswa.xlsx');
    }
}
