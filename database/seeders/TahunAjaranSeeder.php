<?php

namespace Database\Seeders;

use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        TahunAjaran::firstOrCreate(
            ['nama' => '2024/2025'],
            ['tanggal_mulai' => '2024-07-15', 'tanggal_selesai' => '2025-06-30', 'is_aktif' => false]
        );

        TahunAjaran::firstOrCreate(
            ['nama' => '2025/2026'],
            ['tanggal_mulai' => '2025-07-14', 'tanggal_selesai' => '2026-06-30', 'is_aktif' => true]
        );
    }
}
