<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->decimal('saldo', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['kelas_id', 'tahun_ajaran_id']);
        });

        Schema::create('mutasi_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kas_kelas_id')->constrained('kas_kelas')->cascadeOnDelete();
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->decimal('nominal', 12, 2);
            $table->string('keterangan');
            $table->string('bukti_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_kas');
        Schema::dropIfExists('kas_kelas');
    }
};
