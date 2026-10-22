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
        // forceCreate karena role sengaja tidak ada di Fillable.
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'password' => '12345678',
            'role' => 'admin',
        ]);

        User::forceCreate([
            'name' => 'Budi',
            'email' => 'budi@email.com',
            'password' => '12345678',
            'role' => 'user',
        ]);

        $this->call(MenuSeeder::class);
    }
}
