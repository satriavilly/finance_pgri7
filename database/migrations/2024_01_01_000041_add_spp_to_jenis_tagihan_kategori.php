<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE jenis_tagihan DROP CONSTRAINT IF EXISTS jenis_tagihan_kategori_check");
        DB::statement("ALTER TABLE jenis_tagihan ADD CONSTRAINT jenis_tagihan_kategori_check CHECK (kategori IN ('kas_kelas', 'buku_lks', 'kegiatan', 'seragam', 'lainnya', 'spp'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE jenis_tagihan DROP CONSTRAINT IF EXISTS jenis_tagihan_kategori_check");
        DB::statement("ALTER TABLE jenis_tagihan ADD CONSTRAINT jenis_tagihan_kategori_check CHECK (kategori IN ('kas_kelas', 'buku_lks', 'kegiatan', 'seragam', 'lainnya'))");
    }
};
