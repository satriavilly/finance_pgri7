<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'username',
        'no_hp',
        'password',
        'is_active',
        'foto_profil',
    ];

    public function fotoProfilUrl(): string
    {
        return $this->foto_profil
            ? asset('storage/' . $this->foto_profil)
            : '';
    }

    public function inisial(): string
    {
        $parts = explode(' ', trim($this->name));
        $init  = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) {
            $init .= strtoupper(substr(end($parts), 0, 1));
        }
        return $init;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    public function anak(): HasOne
    {
        return $this->hasOne(Siswa::class, 'ortu_user_id');
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }
}
