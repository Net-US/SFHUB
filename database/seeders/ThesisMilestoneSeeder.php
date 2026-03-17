<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ThesisMilestone;

class ThesisMilestoneSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@sfhub.dev')->first();
        if (!$user) return;

        $milestones = [
            ['label' => 'Pengajuan Judul',      'target_date' => 'Jan 2024', 'done' => true,  'is_active' => false, 'sort_order' => 1],
            ['label' => 'Bimbingan Bab 1–2',    'target_date' => 'Feb 2024', 'done' => true,  'is_active' => true,  'sort_order' => 2],
            ['label' => 'Seminar Proposal',      'target_date' => 'Mar 2024', 'done' => false, 'is_active' => false, 'sort_order' => 3],
            ['label' => 'Bimbingan Bab 3–5',    'target_date' => 'Mei 2024', 'done' => false, 'is_active' => false, 'sort_order' => 4],
            ['label' => 'Sidang Akhir',          'target_date' => 'Jun 2024', 'done' => false, 'is_active' => false, 'sort_order' => 5],
        ];

        foreach ($milestones as $m) {
            ThesisMilestone::firstOrCreate(
                ['user_id' => $user->id, 'label' => $m['label']],
                array_merge($m, ['user_id' => $user->id])
            );
        }
    }
}
