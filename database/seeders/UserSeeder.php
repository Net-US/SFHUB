<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;
use App\Models\Workspace;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@sfhub.dev'],
            [
                'name'      => 'Administrator',
                'username'  => 'admin',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'is_active' => true,
                'plan'      => 'pro',
                'preferences' => json_encode(['theme' => 'light', 'language' => 'id']),
            ]
        );
        Profile::firstOrCreate(['user_id' => $admin->id]);

        // Demo student user
        $demo = User::firstOrCreate(
            ['email' => 'demo@sfhub.dev'],
            [
                'name'      => 'Budi Mahasiswa',
                'username'  => 'budimhs',
                'password'  => Hash::make('password'),
                'role'      => 'both',
                'is_active' => true,
                'plan'      => 'free',
                'occupation' => 'Mahasiswa Teknik Informatika',
                'preferences' => json_encode(['theme' => 'light', 'language' => 'id']),
            ]
        );
        Profile::firstOrCreate(['user_id' => $demo->id]);

        // Create default workspaces for demo user
        if ($demo->workspaces()->count() === 0) {
            $workspaces = [
                ['name' => 'Creative Studio', 'type' => 'creative',  'color' => '#f97316', 'icon' => 'palette',         'sort_order' => 1, 'is_active' => true],
                ['name' => 'Academic Hub',    'type' => 'academic',  'color' => '#3b82f6', 'icon' => 'graduation-cap',  'sort_order' => 2, 'is_active' => true],
                ['name' => 'PKL / Work',      'type' => 'pkl',       'color' => '#10b981', 'icon' => 'briefcase',        'sort_order' => 3, 'is_active' => true],
                ['name' => 'Personal',        'type' => 'personal',  'color' => '#8b5cf6', 'icon' => 'user',             'sort_order' => 4, 'is_active' => true],
            ];
            foreach ($workspaces as $ws) {
                $demo->workspaces()->create($ws);
            }
        }

        // Extra demo users
        $extras = [
            ['name' => 'Sari Freelancer', 'username' => 'sarifree', 'email' => 'sari@sfhub.dev',  'role' => 'freelancer'],
            ['name' => 'Andi Student',    'username' => 'andistd',  'email' => 'andi@sfhub.dev',  'role' => 'student'],
            ['name' => 'Test Inactive',   'username' => 'inactive1', 'email' => 'inactive@sfhub.dev', 'role' => 'student', 'is_active' => false],
        ];
        foreach ($extras as $extra) {
            $u = User::firstOrCreate(
                ['email' => $extra['email']],
                array_merge($extra, [
                    'password'  => Hash::make('password'),
                    'is_active' => $extra['is_active'] ?? true,
                    'plan'      => 'free',
                    'preferences' => json_encode(['theme' => 'light', 'language' => 'id']),
                ])
            );
            Profile::firstOrCreate(['user_id' => $u->id]);
        }
    }
}
