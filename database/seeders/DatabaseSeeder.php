<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            TahunAjaranSeeder::class,
            KelasSeeder::class,
            SiswaSeeder::class,
        ]);
    }
}
