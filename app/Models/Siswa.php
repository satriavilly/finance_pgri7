<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis',
        'nama',
        'kelas_id',
        'ortu_user_id',
        'jenis_kelamin',
        'alamat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function ortu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ortu_user_id');
    }

    public function tagihanSiswa(): HasMany
    {
        return $this->hasMany(TagihanSiswa::class);
    }

    public function tagihanBelumLunas(): HasMany
    {
        return $this->hasMany(TagihanSiswa::class)->whereIn('status', ['belum_bayar', 'cicilan']);
    }
}
