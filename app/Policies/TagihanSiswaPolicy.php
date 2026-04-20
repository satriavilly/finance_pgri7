<?php

namespace App\Policies;

use App\Models\TagihanSiswa;
use App\Models\User;

class TagihanSiswaPolicy
{
    public function view(User $user, TagihanSiswa $tagihan): bool
    {
        if ($user->hasRole('siswa')) {
            return $user->siswa?->id === $tagihan->siswa_id;
        }

        if ($user->hasRole('ortu')) {
            return $user->anak?->id === $tagihan->siswa_id;
        }

        if ($user->hasRole('wali_kelas')) {
            return $tagihan->siswa->kelas->wali_kelas_id === $user->id;
        }

        return $user->hasAnyRole(['bendahara', 'kepsek', 'admin']);
    }

    public function bayarTunai(User $user, TagihanSiswa $tagihan): bool
    {
        if (!$user->hasAnyRole(['wali_kelas', 'bendahara'])) {
            return false;
        }
        if ($tagihan->status === 'void' || $tagihan->status === 'lunas') {
            return false;
        }
        if ($user->hasRole('wali_kelas')) {
            return $tagihan->siswa->kelas->wali_kelas_id === $user->id;
        }
        return true;
    }

    public function uploadBukti(User $user, TagihanSiswa $tagihan): bool
    {
        if (!$user->hasRole('siswa')) {
            return false;
        }
        if ($tagihan->status === 'void' || $tagihan->status === 'lunas') {
            return false;
        }
        return $user->siswa?->id === $tagihan->siswa_id;
    }
}
