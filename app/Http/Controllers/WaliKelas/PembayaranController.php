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

    public function daftarSiswa(): View
    {
        $kelas = auth()->user()->kelasWali()
            ->with('tahunAjaran')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->first();

        $siswa = $kelas
            ? \App\Models\Siswa::with([
                'tagihanSiswa' => fn($q) => $q->where('status', '!=', 'void')
                    ->with(['jenisTagihan', 'pembayaran' => fn($q) => $q->where('is_void', false)->where('status_verifikasi', 'approved')->latest()->limit(5)]),
              ])
                ->where('kelas_id', $kelas->id)
                ->orderBy('nama')
                ->get()
            : collect();

        return view('wali-kelas.pembayaran.daftar-siswa', compact('kelas', 'siswa'));
    }

    public function siswaDaftarTagihan(int $siswaId): View
    {
        $siswa = Siswa::with(['kelas', 'tagihanSiswa.jenisTagihan', 'tagihanSiswa.cicilan'])->findOrFail($siswaId);

        return view('wali-kelas.pembayaran.daftar-tagihan', compact('siswa'));
    }

    public function formBayarTunai(int $tagihanId): View
    {
        $tagihan = TagihanSiswa::with(['siswa', 'jenisTagihan', 'cicilan'])->findOrFail($tagihanId);
        $detail = $this->cicilanService->getTagihanDenganCicilan($tagihan);

        return view('wali-kelas.pembayaran.bayar-tunai', compact('tagihan', 'detail'));
    }

    public function bayarTunai(StorePembayaranTunaiRequest $request, int $tagihanId): RedirectResponse
    {
        $tagihan = TagihanSiswa::findOrFail($tagihanId);
        $this->pembayaranService->bayarTunai($tagihan, $request->validated(), auth()->id());

        return redirect()->route('wali-kelas.siswa.tagihan', $tagihan->siswa_id)
            ->with('success', 'Pembayaran tunai berhasil dicatat.');
    }

    public function uploadBuktiWaliKelas(\Illuminate\Http\Request $request, int $tagihanId): RedirectResponse
    {
        $request->validate([
            'metode'        => ['required', 'in:transfer,qris'],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'nominal'       => ['required', 'numeric', 'min:1000'],
            'cicilan_id'    => ['nullable', 'exists:cicilan,id'],
            'bukti_bayar'   => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan'       => ['nullable', 'string', 'max:500'],
        ], [
            'bukti_bayar.required'  => 'File bukti bayar wajib diunggah.',
            'bukti_bayar.mimes'     => 'File harus berformat JPG, PNG, atau PDF.',
            'bukti_bayar.max'       => 'Ukuran file maksimal 2 MB.',
            'nominal.min'           => 'Nominal minimal Rp 1.000.',
            'tanggal_bayar.required'       => 'Tanggal bayar wajib diisi.',
            'tanggal_bayar.before_or_equal' => 'Tanggal bayar tidak boleh lebih dari hari ini.',
        ]);

        $tagihan = TagihanSiswa::findOrFail($tagihanId);
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
