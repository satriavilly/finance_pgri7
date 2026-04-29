<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliKelasController extends Controller
{
    public function index(Request $request): View
    {
        $allTahunAjaran = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        $tahunAjaran = $request->filled('ta')
            ? $allTahunAjaran->firstWhere('id', $request->integer('ta'))
            : TahunAjaran::aktif() ?? $allTahunAjaran->first();

        $kelasList = $tahunAjaran
            ? Kelas::where('tahun_ajaran_id', $tahunAjaran->id)
                ->with('waliKelas')
                ->withCount('siswa')
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->get()
            : collect();

        $waliUsers = User::role('wali_kelas')->orderBy('name')->get();

        return view('admin.wali-kelas.index', compact('allTahunAjaran', 'tahunAjaran', 'kelasList', 'waliUsers'));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $request->validate([
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $kelas->update(['wali_kelas_id' => $request->wali_kelas_id ?: null]);

        $nama = $request->wali_kelas_id
            ? User::find($request->wali_kelas_id)?->name
            : null;

        $msg = $nama
            ? "Wali kelas {$kelas->nama} berhasil diubah ke {$nama}."
            : "Wali kelas {$kelas->nama} berhasil dikosongkan.";

        return back()->with('success', $msg);
    }
}
