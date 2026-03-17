<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@sfhub.dev')->first();
        if (!$user) return;

        $subjects = [
            ['code' => 'IF401', 'name' => 'Metodologi Penelitian',   'sks' => 3, 'semester' => 7, 'day_of_week' => 'Senin',  'start_time' => '08:00', 'end_time' => '10:30', 'room' => 'R.202',  'lecturer' => 'Dr. Ahmad Fauzi, M.Kom',    'progress' => 80, 'notes' => 'UTS minggu depan'],
            ['code' => 'IF405', 'name' => 'Pengolahan Citra Digital', 'sks' => 3, 'semester' => 7, 'day_of_week' => 'Rabu',   'start_time' => '13:00', 'end_time' => '15:30', 'room' => 'Lab B',   'lecturer' => 'Ir. Siti Rahayu, M.T.',     'progress' => 40, 'notes' => 'Tugas filter gambar'],
            ['code' => 'IF499', 'name' => 'Skripsi',                  'sks' => 6, 'semester' => 7, 'day_of_week' => 'Kamis',  'start_time' => '09:00', 'end_time' => '10:00', 'room' => 'R.Dosen', 'lecturer' => 'Prof. Budi Santoso',        'progress' => 15, 'notes' => 'Bimbingan Bab 1'],
            ['code' => 'IF403', 'name' => 'Sistem Terdistribusi',     'sks' => 3, 'semester' => 7, 'day_of_week' => 'Jumat',  'start_time' => '10:00', 'end_time' => '12:30', 'room' => 'R.305',   'lecturer' => 'Drs. Hendra Wijaya',        'progress' => 55, 'notes' => ''],
        ];

        foreach ($subjects as $s) {
            Subject::firstOrCreate(
                ['user_id' => $user->id, 'code' => $s['code']],
                array_merge($s, ['user_id' => $user->id, 'is_active' => true])
            );
        }
    }
}
