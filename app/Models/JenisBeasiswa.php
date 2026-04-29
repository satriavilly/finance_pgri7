<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JenisBeasiswa extends Model
{
    protected $table = 'jenis_beasiswa';

    protected $fillable = ['nama', 'kode', 'deskripsi', 'sumber', 'is_aktif'];

    protected $casts = ['is_aktif' => 'boolean'];

    public static array $sumberOptions = [
        'Pemerintah Pusat',
        'Pemerintah Daerah',
        'Sekolah',
        'Donatur / Yayasan',
        'Lainnya',
    ];

    public static function generateKode(string $nama): string
    {
        $base = Str::slug($nama, '_');
        $kode = $base;
        $i    = 2;
        while (static::where('kode', $kode)->exists()) {
            $kode = $base . '_' . $i++;
        }
        return $kode;
    }
}
