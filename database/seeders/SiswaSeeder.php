<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelas7a = Kelas::where('nama', '7A')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->first();

        $ortuUser = User::whereHas('roles', fn($q) => $q->where('name', 'ortu'))->first();
        $siswaUser = User::whereHas('roles', fn($q) => $q->where('name', 'siswa'))->first();

        if ($kelas7a && $siswaUser) {
            Siswa::firstOrCreate(
                ['nis' => '20240001'],
                [
                    'user_id' => $siswaUser->id,
                    'nama' => 'Budi Santoso',
                    'kelas_id' => $kelas7a->id,
                    'ortu_user_id' => $ortuUser?->id,
                    'jenis_kelamin' => 'L',
                ]
            );
        }

        // Tambah beberapa siswa demo
        $namaDemo = [
            ['nis' => '20240002', 'nama' => 'Siti Rahayu', 'jk' => 'P'],
            ['nis' => '20240003', 'nama' => 'Ahmad Fauzi', 'jk' => 'L'],
            ['nis' => '20240004', 'nama' => 'Dewi Lestari', 'jk' => 'P'],
            ['nis' => '20240005', 'nama' => 'Rizky Pratama', 'jk' => 'L'],
        ];

        foreach ($namaDemo as $data) {
            $user = User::firstOrCreate(
                ['username' => strtolower(str_replace(' ', '_', $data['nama']))],
                [
                    'name' => $data['nama'],
                    'email' => strtolower(str_replace(' ', '.', $data['nama'])) . '@siswa.smppgri7.sch.id',
                    'password' => Hash::make('siswa123'),
                    'is_active' => true,
                ]
            );
            $user->syncRoles(['siswa']);

            if ($kelas7a) {
                Siswa::firstOrCreate(
                    ['nis' => $data['nis']],
                    [
                        'user_id' => $user->id,
                        'nama' => $data['nama'],
                        'kelas_id' => $kelas7a->id,
                        'jenis_kelamin' => $data['jk'],
                    ]
                );
            }
        }
    }
}
