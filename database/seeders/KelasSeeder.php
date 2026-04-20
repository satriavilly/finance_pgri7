<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAjaran = TahunAjaran::where('is_aktif', true)->first();
        $waliKelas = User::whereHas('roles', fn($q) => $q->where('name', 'wali_kelas'))->first();

        $kelas = [
            ['nama' => '7A', 'tingkat' => '7'],
            ['nama' => '7B', 'tingkat' => '7'],
            ['nama' => '8A', 'tingkat' => '8'],
            ['nama' => '8B', 'tingkat' => '8'],
            ['nama' => '9A', 'tingkat' => '9'],
            ['nama' => '9B', 'tingkat' => '9'],
        ];

        foreach ($kelas as $i => $data) {
            Kelas::firstOrCreate(
                ['nama' => $data['nama'], 'tahun_ajaran_id' => $tahunAjaran->id],
                [
                    'tingkat' => $data['tingkat'],
                    'wali_kelas_id' => $i === 0 ? $waliKelas?->id : null,
                ]
            );
        }
    }
}
