<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_siswa_id')->constrained('tagihan_siswa')->cascadeOnDelete();
            $table->foreignId('cicilan_id')->nullable()->constrained('cicilan')->nullOnDelete();
            $table->decimal('nominal', 12, 2);
            $table->enum('metode', ['tunai', 'transfer', 'qris'])->default('tunai');
            $table->string('bukti_bayar_path')->nullable();
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan')->nullable();
            $table->text('catatan_tolak')->nullable();
            $table->boolean('is_void')->default(false);
            $table->text('catatan_void')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
