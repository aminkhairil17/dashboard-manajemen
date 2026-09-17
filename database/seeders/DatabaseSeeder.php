<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // firstOrCreate (bukan updateOrCreate) supaya seeder ini aman dijalankan
        // ulang kapan pun tanpa crash "email sudah dipakai" atau bikin company
        // dobel — dan tidak diam-diam me-reset password direktur yang sudah
        // pernah diganti manual kalau seeder ini ke-trigger lagi di production.
        $direktur = User::firstOrCreate(
            ['email' => 'direktur@syifamedika.id'],
            [
                'name' => 'Direktur RS Syifa Medika',
                'role' => 'manajemen',
                'password' => bcrypt('password'),
            ]
        );

        $syifaMedika = Company::firstOrCreate(
            ['slug' => 'rs-syifa-medika'],
            ['name' => 'RS Syifa Medika', 'bed_capacity' => 120]
        );

        $syifaCabang = Company::firstOrCreate(
            ['slug' => 'rs-syifa-medika-cabang-denpasar'],
            ['name' => 'RS Syifa Medika Cabang Denpasar', 'bed_capacity' => 80]
        );

        $direktur->companies()->syncWithoutDetaching([
            $syifaMedika->id => ['role' => 'direktur'],
            $syifaCabang->id => ['role' => 'direktur'],
        ]);
    }
}
