<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'full_name' => 'Admin User',
            'email' => 'admin@pearlhub.lk',
            'role' => 'admin',
            'password' => bcrypt('secret123'),
        ]);

        User::factory()->create([
            'full_name' => 'Test Provider',
            'email' => 'provider@pearlhub.lk',
            'role' => 'provider',
            'password' => bcrypt('secret123'),
        ]);

        User::factory()->create([
            'full_name' => 'Test Customer',
            'email' => 'customer@pearlhub.lk',
            'role' => 'customer',
            'password' => bcrypt('secret123'),
        ]);

        $this->call(ListingSeeder::class);
    }
}
