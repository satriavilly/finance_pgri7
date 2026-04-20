<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'username' => 'admin', 'email' => 'admin@smppgri7.sch.id', 'role' => 'admin'],
            ['name' => 'Kepala Sekolah', 'username' => 'kepsek', 'email' => 'kepsek@smppgri7.sch.id', 'role' => 'kepsek'],
            ['name' => 'Bendahara Sekolah', 'username' => 'bendahara', 'email' => 'bendahara@smppgri7.sch.id', 'role' => 'bendahara'],
            ['name' => 'Wali Kelas 7A', 'username' => 'wali_kelas', 'email' => 'walikelas7a@smppgri7.sch.id', 'role' => 'wali_kelas'],
            ['name' => 'Budi Santoso', 'username' => 'siswa', 'email' => 'siswa@smppgri7.sch.id', 'role' => 'siswa'],
            ['name' => 'Orang Tua Budi', 'username' => 'ortu', 'email' => 'ortu@smppgri7.sch.id', 'role' => 'ortu'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['username' => $data['username']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['username']),
                    'is_active' => true,
                ]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
