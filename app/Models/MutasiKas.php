<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiKas extends Model
{
    protected $table = 'mutasi_kas';

    protected $fillable = [
        'kas_kelas_id',
        'tipe',
        'nominal',
        'keterangan',
        'bukti_path',
        'created_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function kasKelas(): BelongsTo
    {
        return $this->belongsTo(KasKelas::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
