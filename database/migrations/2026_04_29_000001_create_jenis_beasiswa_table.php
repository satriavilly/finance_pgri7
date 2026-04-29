<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_beasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 200);
            $table->string('kode', 100)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('sumber', 100)->nullable(); // Pemerintah, Sekolah, Donatur, dll
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_beasiswa');
    }
};
