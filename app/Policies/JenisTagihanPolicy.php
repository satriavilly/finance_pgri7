<?php

namespace App\Policies;

use App\Models\JenisTagihan;
use App\Models\User;

class JenisTagihanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['wali_kelas', 'bendahara', 'kepsek', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['wali_kelas', 'bendahara']);
    }

    public function update(User $user, JenisTagihan $jenisTagihan): bool
    {
        if ($user->hasRole('bendahara')) {
            return true;
        }
        if ($user->hasRole('wali_kelas')) {
            return $jenisTagihan->kelas->wali_kelas_id === $user->id;
        }
        return false;
    }
}
