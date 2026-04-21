<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'role' => 'student',
        ]);

        User::factory()->create([
            'name' => 'Canteen Staff',
            'email' => 'staff@example.com',
            'role' => 'staff',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
