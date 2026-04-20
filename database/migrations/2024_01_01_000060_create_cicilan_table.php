<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cicilan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_siswa_id')->constrained('tagihan_siswa')->cascadeOnDelete();
            $table->integer('ke'); // cicilan ke-1, ke-2, dst
            $table->decimal('nominal', 12, 2);
            $table->date('due_date')->nullable();
            $table->enum('status', ['belum_bayar', 'lunas'])->default('belum_bayar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cicilan');
    }
};
