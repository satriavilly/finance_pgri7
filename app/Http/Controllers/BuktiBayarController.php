<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BuktiBayarController extends Controller
{
    public function show(int $id): Response
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $this->authorize('view', $pembayaran->tagihanSiswa);

        if (!$pembayaran->bukti_bayar_path || !Storage::exists($pembayaran->bukti_bayar_path)) {
            abort(404, 'File bukti bayar tidak ditemukan.');
        }

        $content = Storage::get($pembayaran->bukti_bayar_path);
        $mime = Storage::mimeType($pembayaran->bukti_bayar_path);

        return response($content, 200)->header('Content-Type', $mime);
    }
}
