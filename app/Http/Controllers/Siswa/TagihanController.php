<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\UploadBuktiBayarRequest;
use App\Models\TagihanSiswa;
use App\Services\CicilanService;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function __construct(
        private PembayaranService $pembayaranService,
        private CicilanService $cicilanService,
    ) {}

    public function index(): View
    {
        $siswa = auth()->user()->siswa()->with('kelas')->firstOrFail();
        $tagihan = TagihanSiswa::with(['jenisTagihan', 'pembayaran', 'cicilan'])
            ->where('siswa_id', $siswa->id)
            ->latest()
            ->paginate(15);

        return view('siswa.tagihan.index', compact('siswa', 'tagihan'));
    }

    public function show(int $tagihanId): View
    {
        $tagihan = TagihanSiswa::with(['jenisTagihan', 'siswa', 'cicilan', 'pembayaran.verifiedBy'])->findOrFail($tagihanId);
        $this->authorize('view', $tagihan);

        $detail = $this->cicilanService->getTagihanDenganCicilan($tagihan);
        return view('siswa.tagihan.show', compact('tagihan', 'detail'));
    }

    public function formUpload(int $tagihanId): View
    {
        $tagihan = TagihanSiswa::with(['jenisTagihan', 'cicilan'])->findOrFail($tagihanId);
        $this->authorize('uploadBukti', $tagihan);

        return view('siswa.tagihan.upload', compact('tagihan'));
    }

    public function uploadBukti(UploadBuktiBayarRequest $request, int $tagihanId): RedirectResponse
    {
        $tagihan = TagihanSiswa::findOrFail($tagihanId);
        $this->authorize('uploadBukti', $tagihan);

        $this->pembayaranService->uploadBuktiBayar(
            $tagihan,
            $request->validated(),
            $request->file('bukti_bayar'),
            auth()->id()
        );

        return redirect()->route('siswa.tagihan.show', $tagihanId)
            ->with('success', 'Bukti bayar berhasil diupload. Menunggu verifikasi wali kelas.');
    }
}
