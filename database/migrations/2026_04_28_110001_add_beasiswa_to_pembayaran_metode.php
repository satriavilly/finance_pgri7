<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: drop old CHECK constraint then re-add with 'beasiswa'
        try {
            DB::statement("ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_metode_check");
        } catch (\Exception) {}

        try {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->enum('metode', ['tunai', 'transfer', 'qris', 'beasiswa'])->default('tunai')->change();
            });
        } catch (\Exception) {
            // PostgreSQL fallback: add constraint directly
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_metode_check CHECK (metode IN ('tunai','transfer','qris','beasiswa'))");
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_metode_check");
        } catch (\Exception) {}

        try {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->enum('metode', ['tunai', 'transfer', 'qris'])->default('tunai')->change();
            });
        } catch (\Exception) {
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_metode_check CHECK (metode IN ('tunai','transfer','qris'))");
        }
    }
};
