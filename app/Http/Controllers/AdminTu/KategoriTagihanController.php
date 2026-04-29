<?php

namespace App\Http\Controllers\AdminTu;

use App\Http\Controllers\Controller;
use App\Models\JenisTagihan;
use App\Models\KategoriTagihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriTagihanController extends Controller
{
    public function index(): View
    {
        $kategoriList = KategoriTagihan::withCount([
                'jenisTagihan as jumlah_tagihan' => fn($q) => $q->whereNull('deleted_at'),
            ])
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        return view('admin-tu.kategori.index', [
            'kategoriList'  => $kategoriList,
            'warnaOptions'  => KategoriTagihan::$warnaOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'  => ['required', 'string', 'max:100'],
            'warna' => ['required', 'string'],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
        ]);

        $kode = KategoriTagihan::generateKode($request->nama);

        $urutan = KategoriTagihan::max('urutan') + 1;

        KategoriTagihan::create([
            'nama'   => $request->nama,
            'kode'   => $kode,
            'warna'  => $request->warna,
            'urutan' => $urutan,
        ]);

        return back()->with('success', "Kategori \"{$request->nama}\" berhasil ditambahkan.");
    }

    public function update(Request $request, KategoriTagihan $kategori): RedirectResponse
    {
        $request->validate([
            'nama'  => ['required', 'string', 'max:100'],
            'warna' => ['required', 'string'],
        ]);

        $kategori->update([
            'nama'  => $request->nama,
            'warna' => $request->warna,
        ]);

        return back()->with('success', "Kategori \"{$kategori->nama}\" berhasil diperbarui.");
    }

    public function destroy(KategoriTagihan $kategori): RedirectResponse
    {
        $dipakai = JenisTagihan::where('kategori', $kategori->kode)->whereNull('deleted_at')->count();

        if ($dipakai > 0) {
            return back()->with('error', "Kategori \"{$kategori->nama}\" tidak bisa dihapus karena masih digunakan oleh {$dipakai} tagihan.");
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return back()->with('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }
}
