<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class KategoriTagihan extends Model
{
    protected $table = 'kategori_tagihan';

    protected $fillable = ['nama', 'kode', 'warna', 'urutan'];

    public static array $warnaOptions = [
        'bg-indigo-100 text-indigo-700' => 'Indigo',
        'bg-blue-100 text-blue-700'     => 'Biru',
        'bg-cyan-100 text-cyan-700'     => 'Cyan',
        'bg-purple-100 text-purple-700' => 'Ungu',
        'bg-orange-100 text-orange-700' => 'Oranye',
        'bg-teal-100 text-teal-700'     => 'Teal',
        'bg-green-100 text-green-700'   => 'Hijau',
        'bg-red-100 text-red-700'       => 'Merah',
        'bg-yellow-100 text-yellow-700' => 'Kuning',
        'bg-pink-100 text-pink-700'     => 'Pink',
        'bg-gray-100 text-gray-600'     => 'Abu-abu',
    ];

    public function jenisTagihan(): HasMany
    {
        return $this->hasMany(JenisTagihan::class, 'kategori', 'kode');
    }

    /** kode => nama */
    public static function labelMap(): array
    {
        return static::orderBy('urutan')->orderBy('nama')->pluck('nama', 'kode')->toArray();
    }

    /** kode => warna (Tailwind classes) */
    public static function warnaMap(): array
    {
        return static::orderBy('urutan')->orderBy('nama')->pluck('warna', 'kode')->toArray();
    }

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
