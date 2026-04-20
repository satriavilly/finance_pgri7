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

    public function siswaDaftarTagihan(int $siswaId): View
    {
        $siswa = Siswa::with(['kelas', 'tagihanSiswa.jenisTagihan', 'tagihanSiswa.cicilan'])->findOrFail($siswaId);
        $this->authorize('viewTagihan', $siswa);

        return view('wali-kelas.pembayaran.daftar-tagihan', compact('siswa'));
    }

    public function formBayarTunai(int $tagihanId): View
    {
        $tagihan = TagihanSiswa::with(['siswa', 'jenisTagihan', 'cicilan'])->findOrFail($tagihanId);
        $this->authorize('bayarTunai', $tagihan);

        $detail = $this->cicilanService->getTagihanDenganCicilan($tagihan);
        return view('wali-kelas.pembayaran.bayar-tunai', compact('tagihan', 'detail'));
    }

    public function bayarTunai(StorePembayaranTunaiRequest $request, int $tagihanId): RedirectResponse
    {
        $tagihan = TagihanSiswa::findOrFail($tagihanId);
        $this->authorize('bayarTunai', $tagihan);

        $this->pembayaranService->bayarTunai($tagihan, $request->validated(), auth()->id());

        return redirect()->route('wali-kelas.siswa.tagihan', $tagihan->siswa_id)
            ->with('success', 'Pembayaran tunai berhasil dicatat.');
    }

    public function verifikasiBuktiBayar(): View
    {
        $this->authorize('verifikasiBuktiBayar', Pembayaran::class);

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
        $this->authorize('verifikasiBuktiBayar', $pembayaran);

        $this->pembayaranService->approveVerifikasi($pembayaran, auth()->id());

        return back()->with('success', 'Bukti bayar disetujui.');
    }

    public function reject(int $pembayaranId): RedirectResponse
    {
        $pembayaran = Pembayaran::findOrFail($pembayaranId);
        $this->authorize('verifikasiBuktiBayar', $pembayaran);

        request()->validate(['catatan_tolak' => ['required', 'string', 'max:500']]);
        $this->pembayaranService->rejectVerifikasi($pembayaran, request('catatan_tolak'), auth()->id());

        return back()->with('success', 'Bukti bayar ditolak.');
    }
}
