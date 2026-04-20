<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['kas_kelas', 'buku_lks', 'kegiatan', 'seragam', 'lainnya'])->default('lainnya');
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->decimal('total_nominal', 12, 2);
            $table->boolean('is_cicilan')->default(false);
            $table->integer('jumlah_cicilan')->default(1);
            $table->date('due_date')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_tagihan');
    }
};
