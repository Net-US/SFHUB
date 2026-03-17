<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@sfhub.dev')->first();
        if (!$user || $user->schedules()->count() > 0) return;

        $schedules = [
            ['day' => 'Senin',  'activity' => 'PKL Pagi',                 'start_time' => '08:00', 'end_time' => '12:00', 'type' => 'pkl',      'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Senin',  'activity' => 'PKL Siang',                'start_time' => '13:00', 'end_time' => '17:00', 'type' => 'pkl',      'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Selasa', 'activity' => 'PKL Full Day',             'start_time' => '08:00', 'end_time' => '17:00', 'type' => 'pkl',      'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Rabu',   'activity' => 'PKL Full Day',             'start_time' => '08:00', 'end_time' => '17:00', 'type' => 'pkl',      'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Kamis',  'activity' => 'PKL Half Day',             'start_time' => '08:00', 'end_time' => '12:00', 'type' => 'pkl',      'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Kamis',  'activity' => 'Kuliah Siang',             'start_time' => '13:00', 'end_time' => '17:00', 'type' => 'academic', 'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Kamis',  'activity' => 'Bimbingan Skripsi',        'start_time' => '17:00', 'end_time' => '18:00', 'type' => 'skripsi',  'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Jumat',  'activity' => 'PKL Half Day',             'start_time' => '08:00', 'end_time' => '12:00', 'type' => 'pkl',      'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Jumat',  'activity' => 'Kuliah Siang',             'start_time' => '13:00', 'end_time' => '17:00', 'type' => 'academic', 'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Senin',  'activity' => 'Skripsi – Deep Work',      'start_time' => '19:00', 'end_time' => '22:00', 'type' => 'skripsi',  'is_recurring' => true,  'frequency' => 'daily'],
            ['day' => 'Minggu', 'activity' => 'Upload Konten IG',         'start_time' => '20:00', 'end_time' => '21:00', 'type' => 'creative', 'is_recurring' => true,  'frequency' => 'weekly'],
            ['day' => 'Senin',  'activity' => 'Persiapan Pagi',           'start_time' => '06:00', 'end_time' => '07:30', 'type' => 'personal', 'is_recurring' => true,  'frequency' => 'daily'],
        ];

        foreach ($schedules as $s) {
            $user->schedules()->create($s);
        }
    }
}
