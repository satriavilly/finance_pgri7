<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cicilan extends Model
{
    protected $table = 'cicilan';

    protected $fillable = [
        'tagihan_siswa_id',
        'ke',
        'nominal',
        'due_date',
        'status',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function tagihanSiswa(): BelongsTo
    {
        return $this->belongsTo(TagihanSiswa::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
