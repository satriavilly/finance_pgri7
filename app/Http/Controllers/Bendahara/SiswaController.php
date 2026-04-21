<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\View\View;

class SiswaController extends Controller
{
    public function siswaKelas(Kelas $kelas): View
    {
        $kelas->load('tahunAjaran');

        $siswa = Siswa::with([
            'tagihanSiswa' => fn($q) => $q->where('status', '!=', 'void')
                ->with(['jenisTagihan', 'pembayaran' => fn($q) => $q->where('is_void', false)->latest()]),
        ])
            ->where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();

        return view('bendahara.siswa-kelas', compact('kelas', 'siswa'));
    }
}
