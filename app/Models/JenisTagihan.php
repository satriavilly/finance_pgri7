<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisTagihan extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_tagihan';

    protected $fillable = [
        'nama',
        'deskripsi',
        'kategori',
        'kelas_id',
        'total_nominal',
        'is_cicilan',
        'jumlah_cicilan',
        'due_date',
        'is_aktif',
        'created_by',
    ];

    protected $casts = [
        'total_nominal' => 'decimal:2',
        'is_cicilan' => 'boolean',
        'is_aktif' => 'boolean',
        'due_date' => 'date',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tagihanSiswa(): HasMany
    {
        return $this->hasMany(TagihanSiswa::class);
    }

    public static function kategoriLabel(): array
    {
        return [
            'spp'       => 'SPP',
            'kas_kelas' => 'Kas Kelas',
            'buku_lks'  => 'Buku & LKS',
            'kegiatan'  => 'Kegiatan',
            'seragam'   => 'Seragam',
            'lainnya'   => 'Lainnya',
        ];
    }
}
