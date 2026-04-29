<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode', 50)->unique();
            $table->string('warna', 120)->default('bg-gray-100 text-gray-600');
            $table->unsignedSmallInteger('urutan')->default(99);
            $table->timestamps();
        });

        DB::table('kategori_tagihan')->insert([
            ['nama' => 'SPP',       'kode' => 'spp',       'warna' => 'bg-indigo-100 text-indigo-700', 'urutan' => 1,  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kas Kelas', 'kode' => 'kas_kelas', 'warna' => 'bg-cyan-100 text-cyan-700',     'urutan' => 2,  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Buku & LKS','kode' => 'buku_lks',  'warna' => 'bg-purple-100 text-purple-700', 'urutan' => 3,  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kegiatan',  'kode' => 'kegiatan',  'warna' => 'bg-orange-100 text-orange-700', 'urutan' => 4,  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Seragam',   'kode' => 'seragam',   'warna' => 'bg-teal-100 text-teal-700',     'urutan' => 5,  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Lainnya',   'kode' => 'lainnya',   'warna' => 'bg-gray-100 text-gray-600',     'urutan' => 99, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_tagihan');
    }
};
