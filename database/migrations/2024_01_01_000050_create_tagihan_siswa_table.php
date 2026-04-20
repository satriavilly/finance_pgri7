<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('jenis_tagihan_id')->constrained('jenis_tagihan')->cascadeOnDelete();
            $table->decimal('nominal_total', 12, 2);
            $table->decimal('nominal_terbayar', 12, 2)->default(0);
            $table->enum('status', ['belum_bayar', 'cicilan', 'lunas', 'void'])->default('belum_bayar');
            $table->date('due_date')->nullable();
            $table->text('catatan_void')->nullable();
            $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('void_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_siswa');
    }
};
