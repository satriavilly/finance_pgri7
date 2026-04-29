<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop PostgreSQL CHECK constraint that restricted kategori values
        try {
            DB::statement('ALTER TABLE jenis_tagihan DROP CONSTRAINT IF EXISTS jenis_tagihan_kategori_check');
        } catch (\Exception $e) {
            // Silently ignore — MySQL uses ENUM not CHECK constraints
        }

        Schema::table('jenis_tagihan', function (Blueprint $table) {
            // Change kategori from ENUM/constrained to free varchar
            $table->string('kategori', 50)->default('lainnya')->change();
            // Allow null so "bebas cicilan" can be represented without a count
            $table->integer('jumlah_cicilan')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('jenis_tagihan', function (Blueprint $table) {
            $table->integer('jumlah_cicilan')->nullable(false)->default(1)->change();
        });
    }
};
