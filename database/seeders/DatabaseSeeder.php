<?php

namespace Database\Seeders;

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
        User::factory()->create([
            'name' => 'Direktur RS Syifa Medika',
            'email' => 'direktur@syifamedika.id',
            'role' => 'manajemen',
            'password' => bcrypt('password'),
        ]);
    }
}
