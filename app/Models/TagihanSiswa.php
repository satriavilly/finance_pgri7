<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanSiswa extends Model
{
    protected $table = 'tagihan_siswa';

    protected $fillable = [
        'siswa_id',
        'jenis_tagihan_id',
        'nominal_total',
        'nominal_terbayar',
        'status',
        'due_date',
        'catatan_void',
        'void_by',
        'void_at',
    ];

    protected $casts = [
        'nominal_total' => 'decimal:2',
        'nominal_terbayar' => 'decimal:2',
        'due_date' => 'date',
        'void_at' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jenisTagihan(): BelongsTo
    {
        return $this->belongsTo(JenisTagihan::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function cicilan(): HasMany
    {
        return $this->hasMany(Cicilan::class);
    }

    public function getSisaTagihanAttribute(): float
    {
        return (float) $this->nominal_total - (float) $this->nominal_terbayar;
    }

    public static function statusLabel(): array
    {
        return [
            'belum_bayar' => 'Belum Bayar',
            'cicilan' => 'Cicilan',
            'lunas' => 'Lunas',
            'void' => 'Void',
        ];
    }
}
