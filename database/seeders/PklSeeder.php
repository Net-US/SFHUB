<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PklInfo;
use App\Models\PklSchedule;
use App\Models\PklLog;

class PklSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@sfhub.dev')->first();
        if (!$user) return;

        // PKL Info
        $pklInfo = PklInfo::firstOrCreate(
            ['user_id' => $user->id, 'company' => 'PT. Digital Kreatif Indonesia'],
            [
                'user_id'          => $user->id,
                'company'          => 'PT. Digital Kreatif Indonesia',
                'department'       => 'IT & Digital Marketing',
                'supervisor'       => 'Bpk. Reza Pratama, S.Kom',
                'supervisor_phone' => '081234567890',
                'address'          => 'Jl. Sudirman No. 45, Jakarta Selatan',
                'start_date'       => '2024-01-08',
                'end_date'         => '2024-06-28',
                'hours_required'   => 720,
                'allowance'        => 2500000,
                'is_active'        => true,
            ]
        );

        // PKL Schedules
        $schedules = [
            ['day' => 'Senin',  'start_time' => '08:00', 'end_time' => '17:00', 'type' => 'full', 'notes' => ''],
            ['day' => 'Selasa', 'start_time' => '08:00', 'end_time' => '17:00', 'type' => 'full', 'notes' => ''],
            ['day' => 'Rabu',   'start_time' => '08:00', 'end_time' => '17:00', 'type' => 'full', 'notes' => ''],
            ['day' => 'Kamis',  'start_time' => '08:00', 'end_time' => '12:00', 'type' => 'half', 'notes' => 'Siang kuliah'],
            ['day' => 'Jumat',  'start_time' => '08:00', 'end_time' => '12:00', 'type' => 'half', 'notes' => 'Siang kuliah'],
            ['day' => 'Sabtu',  'start_time' => null,    'end_time' => null,    'type' => 'off',  'notes' => 'Libur'],
            ['day' => 'Minggu', 'start_time' => null,    'end_time' => null,    'type' => 'off',  'notes' => 'Libur'],
        ];

        if ($user->pklSchedules()->count() === 0) {
            foreach ($schedules as $s) {
                $user->pklSchedules()->create($s);
            }
        }

        // PKL Activities
        $activities = [
            ['log_date' => now()->format('Y-m-d'),       'task' => 'Membuat desain banner website perusahaan',  'category' => 'Design',         'hours' => 4, 'status' => 'done', 'notes' => 'Menggunakan Figma'],
            ['log_date' => now()->subDay()->format('Y-m-d'), 'task' => 'Rapat koordinasi tim IT',              'category' => 'Meeting',        'hours' => 1, 'status' => 'done', 'notes' => ''],
            ['log_date' => now()->subDays(2)->format('Y-m-d'),'task' => 'Update konten media sosial bulanan', 'category' => 'Social Media',   'hours' => 3, 'status' => 'done', 'notes' => 'IG & LinkedIn'],
            ['log_date' => now()->subDays(3)->format('Y-m-d'),'task' => 'Laporan mingguan ke supervisor',     'category' => 'Administration', 'hours' => 1, 'status' => 'done', 'notes' => ''],
            ['log_date' => now()->addDay()->format('Y-m-d'),  'task' => 'Presentasi progres PKL bulan pertama','category' => 'Presentation',  'hours' => 2, 'status' => 'todo', 'notes' => 'Siapkan slide'],
            ['log_date' => now()->subDays(4)->format('Y-m-d'),'task' => 'Riset kompetitor website klien',     'category' => 'Development',    'hours' => 3, 'status' => 'done', 'notes' => ''],
            ['log_date' => now()->subDays(5)->format('Y-m-d'),'task' => 'Setup environment development',     'category' => 'Development',    'hours' => 4, 'status' => 'done', 'notes' => 'Install Laravel + Vue'],
        ];

        foreach ($activities as $a) {
            if (!PklLog::where('user_id', $user->id)->where('task', $a['task'])->exists()) {
                $user->pklLogs()->create(array_merge($a, ['user_id' => $user->id]));
            }
        }
    }
}
