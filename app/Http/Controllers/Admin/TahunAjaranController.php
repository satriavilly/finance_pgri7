<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    public function index(): View
    {
        $tahunAjaranList = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        return view('admin.tahun-ajaran.index', compact('tahunAjaranList'));
    }

    public function create(): View
    {
        return view('admin.tahun-ajaran.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama'            => 'required|string|max:100|unique:tahun_ajaran,nama',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        $data['is_aktif'] = false;

        TahunAjaran::create($data);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran): View
    {
        return view('admin.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $data = $request->validate([
            'nama'            => 'required|string|max:100|unique:tahun_ajaran,nama,' . $tahunAjaran->id,
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        $tahunAjaran->update($data);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function aktifkan(TahunAjaran $tahunAjaran): RedirectResponse
    {
        // Nonaktifkan semua, lalu aktifkan yang dipilih
        TahunAjaran::query()->update(['is_aktif' => false]);
        $tahunAjaran->update(['is_aktif' => true]);

        return back()->with('success', "Tahun ajaran \"{$tahunAjaran->nama}\" sekarang aktif. Semua tagihan dan transaksi baru akan menggunakan tahun ajaran ini.");
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        if ($tahunAjaran->is_aktif) {
            return back()->with('error', 'Tahun ajaran aktif tidak dapat dihapus. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        if ($tahunAjaran->kelas()->exists()) {
            return back()->with('error', 'Tahun ajaran ini memiliki data kelas dan tidak dapat dihapus.');
        }

        $tahunAjaran->delete();

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
