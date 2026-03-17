<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin StudentHub',
            'email' => 'admin@studenthub.com',
            'password' => Hash::make('password123'),
            'role' => 'both',
            'plan' => 'pro',
            'preferences' => json_encode([
                'theme' => 'light',
                'notifications' => true,
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]),
        ]);

        // Create demo user
        User::create([
            'name' => 'Mahasiswa Kreatif',
            'email' => 'demo@studenthub.com',
            'password' => Hash::make('password123'),
            'role' => 'both',
            'plan' => 'pro',
            'preferences' => json_encode([
                'theme' => 'light',
                'notifications' => true,
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]),
        ]);

        // Call other seeders
        $this->call([
            ProfileSeeder::class,
            WorkspaceSeeder::class,
            NewEntitiesSeeder::class,
        ]);
    }
}
