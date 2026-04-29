<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\JenisBeasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JenisBeasiswaController extends Controller
{
    public function index(): View
    {
        $list = JenisBeasiswa::orderBy('nama')->get();
        return view('bendahara.jenis-beasiswa.index', [
            'list'          => $list,
            'sumberOptions' => JenisBeasiswa::$sumberOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'    => ['required', 'string', 'max:200', 'unique:jenis_beasiswa,nama'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'sumber'  => ['nullable', 'string', 'max:100'],
        ]);

        JenisBeasiswa::create([
            'nama'      => $request->nama,
            'kode'      => JenisBeasiswa::generateKode($request->nama),
            'deskripsi' => $request->deskripsi,
            'sumber'    => $request->sumber,
            'is_aktif'  => true,
        ]);

        return back()->with('success', 'Jenis beasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, JenisBeasiswa $jenisBeasiswa): RedirectResponse
    {
        $request->validate([
            'nama'      => ['required', 'string', 'max:200', Rule::unique('jenis_beasiswa', 'nama')->ignore($jenisBeasiswa->id)],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'sumber'    => ['nullable', 'string', 'max:100'],
            'is_aktif'  => ['boolean'],
        ]);

        $jenisBeasiswa->update($request->only('nama', 'deskripsi', 'sumber', 'is_aktif'));

        return back()->with('success', 'Jenis beasiswa berhasil diperbarui.');
    }

    public function destroy(JenisBeasiswa $jenisBeasiswa): RedirectResponse
    {
        $jenisBeasiswa->delete();
        return back()->with('success', 'Jenis beasiswa berhasil dihapus.');
    }

    public function toggleAktif(JenisBeasiswa $jenisBeasiswa): RedirectResponse
    {
        $jenisBeasiswa->update(['is_aktif' => !$jenisBeasiswa->is_aktif]);
        $label = $jenisBeasiswa->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Beasiswa \"{$jenisBeasiswa->nama}\" {$label}.");
    }
}
