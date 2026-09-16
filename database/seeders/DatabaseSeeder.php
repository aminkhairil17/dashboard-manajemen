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
        $direktur = User::factory()->create([
            'name' => 'Direktur RS Syifa Medika',
            'email' => 'direktur@syifamedika.id',
            'role' => 'manajemen',
            'password' => bcrypt('password'),
        ]);

        $syifaMedika = Company::create([
            'name' => 'RS Syifa Medika',
            'slug' => 'rs-syifa-medika',
            'bed_capacity' => 120,
        ]);

        $syifaCabang = Company::create([
            'name' => 'RS Syifa Medika Cabang Denpasar',
            'slug' => 'rs-syifa-medika-cabang-denpasar',
            'bed_capacity' => 80,
        ]);

        $direktur->companies()->attach([
            $syifaMedika->id => ['role' => 'direktur'],
            $syifaCabang->id => ['role' => 'direktur'],
        ]);
    }
}
