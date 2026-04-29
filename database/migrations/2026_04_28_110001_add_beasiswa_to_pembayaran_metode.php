<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_metode_check");
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_metode_check CHECK (metode IN ('tunai','transfer','qris','beasiswa'))");
        } else {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->enum('metode', ['tunai', 'transfer', 'qris', 'beasiswa'])->default('tunai')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_metode_check");
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_metode_check CHECK (metode IN ('tunai','transfer','qris'))");
        } else {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->enum('metode', ['tunai', 'transfer', 'qris'])->default('tunai')->change();
            });
        }
    }
};
