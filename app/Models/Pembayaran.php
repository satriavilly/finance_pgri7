<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_siswa_id',
        'cicilan_id',
        'nominal',
        'metode',
        'bukti_bayar_path',
        'status_verifikasi',
        'verified_by',
        'verified_at',
        'catatan',
        'catatan_tolak',
        'is_void',
        'catatan_void',
        'created_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'verified_at' => 'datetime',
        'is_void' => 'boolean',
    ];

    public function tagihanSiswa(): BelongsTo
    {
        return $this->belongsTo(TagihanSiswa::class);
    }

    public function cicilan(): BelongsTo
    {
        return $this->belongsTo(Cicilan::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function metodeLabel(): array
    {
        return [
            'tunai'    => 'Tunai',
            'transfer' => 'Transfer Bank',
            'qris'     => 'QRIS',
            'beasiswa' => 'Beasiswa / Subsidi',
        ];
    }

    public static function statusVerifikasiLabel(): array
    {
        return [
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
    }
}
