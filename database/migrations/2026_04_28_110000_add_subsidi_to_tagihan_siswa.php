<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            $table->decimal('nominal_subsidi', 12, 2)->default(0)->after('nominal_terbayar');
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_siswa', function (Blueprint $table) {
            $table->dropColumn('nominal_subsidi');
        });
    }
};
