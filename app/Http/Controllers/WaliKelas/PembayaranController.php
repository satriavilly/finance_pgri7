<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Http\Requests\WaliKelas\StorePembayaranTunaiRequest;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TagihanSiswa;
use App\Services\CicilanService;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function __construct(
        private PembayaranService $pembayaranService,
        private CicilanService $cicilanService,
    ) {}

    public function daftarSiswa(\Illuminate\Http\Request $request): View
    {
        $allTahunAjaran = \App\Models\TahunAjaran::orderByDesc('tanggal_mulai')->get();
        $selectedTa = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : \App\Models\TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $kelas = auth()->user()->kelasWali()
            ->with('tahunAjaran')
            ->when($selectedTa, fn($q) => $q->where('tahun_ajaran_id', $selectedTa->id))
            ->first();

        $siswa = $kelas
            ? \App\Models\Siswa::with([
                'user',
                'tagihanSiswa' => fn($q) => $q->where('status', '!=', 'void')
                    ->with('jenisTagihan'),
              ])
                ->where('kelas_id', $kelas->id)
                ->orderBy('nama')
                ->get()
            : collect();

        return view('wali-kelas.pembayaran.daftar-siswa', compact('kelas', 'siswa', 'allTahunAjaran', 'selectedTa'));
    }

    public function siswaDaftarTagihan(int $siswaId): View
    {
        $periodeAktif = now()->format('Y-m'); // e.g. "2026-04"

        $siswa = Siswa::with([
            'kelas',
            'tagihanSiswa' => fn($q) => $q
                ->where('status', '!=', 'void')
                ->where(fn($q2) => $q2
                    // SPP: tampil jika deskripsi (YYYY-MM) <= bulan berjalan
                    ->whereHas('jenisTagihan', fn($q3) => $q3
                        ->where('kategori', 'spp')
                        ->where('deskripsi', '<=', $periodeAktif)
                    )
                    // Non-SPP: selalu tampil
                    ->orWhereHas('jenisTagihan', fn($q3) => $q3->where('kategori', '!=', 'spp'))
                )
                ->orderBy('id')
                ->with([
                    'jenisTagihan',
                    'cicilan',
                    'pembayaran' => fn($q) => $q->where('is_void', false)->latest(),
                ]),
        ])->findOrFail($siswaId);

        return view('wali-kelas.pembayaran.daftar-tagihan', compact('siswa'));
    }

    public function formBayarTunai(int $tagihanId): View
    {
        $tagihan = TagihanSiswa::with(['siswa', 'jenisTagihan.kelas.tahunAjaran', 'cicilan'])->findOrFail($tagihanId);
        abort_if($tagihan->jenisTagihan->kategori === 'spp', 403, 'Pembayaran SPP dilakukan melalui Bendahara.');
        $detail = $this->cicilanService->getTagihanDenganCicilan($tagihan);

        return view('wali-kelas.pembayaran.bayar-tunai', compact('tagihan', 'detail'));
    }

    public function bayarTunai(StorePembayaranTunaiRequest $request, int $tagihanId): RedirectResponse
    {
        $tagihan = TagihanSiswa::with('jenisTagihan')->findOrFail($tagihanId);
        abort_if($tagihan->jenisTagihan->kategori === 'spp', 403, 'Pembayaran SPP dilakukan melalui Bendahara.');
        $this->pembayaranService->bayarTunai($tagihan, $request->validated(), auth()->id());

        return redirect()->route('wali-kelas.siswa.tagihan', $tagihan->siswa_id)
            ->with('success', 'Pembayaran tunai berhasil dicatat.');
    }

    public function uploadBuktiWaliKelas(\Illuminate\Http\Request $request, int $tagihanId): RedirectResponse
    {
        $tagihan = TagihanSiswa::with('jenisTagihan')->findOrFail($tagihanId);

        $request->validate([
            'metode'        => ['required', 'in:transfer,qris'],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'nominal'       => [
                'required', 'numeric', 'min:1000',
                function ($attr, $value, $fail) use ($tagihan) {
                    if ($value > $tagihan->sisa_tagihan) {
                        $fail('Nominal melebihi sisa tagihan (Rp ' . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ').');
                    }
                },
            ],
            'cicilan_id'    => ['nullable', 'exists:cicilan,id'],
            'bukti_bayar'   => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan'       => ['nullable', 'string', 'max:500'],
        ], [
            'bukti_bayar.required'           => 'File bukti bayar wajib diunggah.',
            'bukti_bayar.mimes'              => 'File harus berformat JPG, PNG, atau PDF.',
            'bukti_bayar.max'                => 'Ukuran file maksimal 2 MB.',
            'nominal.min'                    => 'Nominal minimal Rp 1.000.',
            'tanggal_bayar.required'         => 'Tanggal bayar wajib diisi.',
            'tanggal_bayar.before_or_equal'  => 'Tanggal bayar tidak boleh lebih dari hari ini.',
        ]);
        abort_if($tagihan->jenisTagihan->kategori === 'spp', 403, 'Pembayaran SPP dilakukan melalui Bendahara.');
        $this->pembayaranService->uploadBuktiBayar(
            $tagihan,
            $request->only('metode', 'tanggal_bayar', 'nominal', 'cicilan_id', 'catatan'),
            $request->file('bukti_bayar'),
            auth()->id(),
            autoApprove: true
        );

        return redirect()->route('wali-kelas.siswa.tagihan', $tagihan->siswa_id)
            ->with('success', 'Pembayaran berhasil dicatat beserta bukti bayar.');
    }

    public function verifikasiBuktiBayar(): View
    {
        $kelas = auth()->user()->kelasWali()->first();
        $pembayaran = Pembayaran::with(['tagihanSiswa.siswa', 'tagihanSiswa.jenisTagihan'])
            ->where('status_verifikasi', 'pending')
            ->where('is_void', false)
            ->whereHas('tagihanSiswa.siswa', fn($q) => $q->where('kelas_id', $kelas?->id))
            ->latest()
            ->paginate(10);

        return view('wali-kelas.pembayaran.verifikasi', compact('pembayaran'));
    }

    public function approve(int $pembayaranId): RedirectResponse
    {
        $pembayaran = Pembayaran::findOrFail($pembayaranId);
        $this->pembayaranService->approveVerifikasi($pembayaran, auth()->id());

        return back()->with('success', 'Bukti bayar disetujui.');
    }

    public function reject(int $pembayaranId): RedirectResponse
    {
        $pembayaran = Pembayaran::findOrFail($pembayaranId);
        request()->validate(['catatan_tolak' => ['required', 'string', 'max:500']]);
        $this->pembayaranService->rejectVerifikasi($pembayaran, request('catatan_tolak'), auth()->id());

        return back()->with('success', 'Bukti bayar ditolak.');
    }

    public function void(int $pembayaranId): RedirectResponse
    {
        $pembayaran = Pembayaran::with('tagihanSiswa.siswa')->findOrFail($pembayaranId);

        request()->validate(['catatan_void' => ['required', 'string', 'max:500']]);

        $this->pembayaranService->voidPembayaran($pembayaran, request('catatan_void'));

        return back()->with('success', 'Pembayaran berhasil dibatalkan (void).');
    }
}
