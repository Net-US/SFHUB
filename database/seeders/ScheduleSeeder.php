<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ScheduleSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();

        if ($user) {
            // Create sample schedules
            $schedules = [
                [
                    'day' => 'Senin',
                    'start_time' => '08:00',
                    'end_time' => '12:00',
                    'activity' => 'PKL Full Day',
                    'type' => 'pkl',
                    'location' => 'Kantor',
                    'is_recurring' => true,
                ],
                [
                    'day' => 'Senin',
                    'start_time' => '13:00',
                    'end_time' => '17:00',
                    'activity' => 'Kuliah Metodologi Penelitian',
                    'type' => 'academic',
                    'location' => 'Kampus',
                    'is_recurring' => true,
                ],
                [
                    'day' => 'Rabu',
                    'start_time' => '19:00',
                    'end_time' => '22:00',
                    'activity' => 'Skripsi / Proyek Kreatif',
                    'type' => 'creative',
                    'is_recurring' => true,
                ],
                // Jadwal dari gambar - Kamis
                [
                    'day' => 'Kamis',
                    'start_time' => '15:40',
                    'end_time' => '17:50',
                    'activity' => 'Professional Development',
                    'type' => 'academic',
                    'location' => 'R.113',
                    'is_recurring' => true,
                ],
                // Jadwal dari gambar - Jumat
                [
                    'day' => 'Jumat',
                    'start_time' => '13:20',
                    'end_time' => '15:30',
                    'activity' => 'Internet of Thing',
                    'type' => 'academic',
                    'location' => 'R.104',
                    'is_recurring' => true,
                ],
                [
                    'day' => 'Jumat',
                    'start_time' => '15:40',
                    'end_time' => '17:50',
                    'activity' => 'Teknik Penulisan Laporan Ilmiah',
                    'type' => 'academic',
                    'location' => 'R.113',
                    'is_recurring' => true,
                ],
                // Jadwal dari gambar - Sabtu
                [
                    'day' => 'Sabtu',
                    'start_time' => '09:50',
                    'end_time' => '13:10',
                    'activity' => 'Otomasi dan Pemrograman Jaringan',
                    'type' => 'academic',
                    'location' => 'R.305 (Lab.Multimedia 2)',
                    'is_recurring' => true,
                ],
            ];

            foreach ($schedules as $schedule) {
                Schedule::create(array_merge($schedule, ['user_id' => $user->id]));
            }

            // Create sample tasks
            $tasks = [
                [
                    'title' => 'Finalisasi Bab 1 Skripsi',
                    'category' => 'academic',
                    'priority' => 'urgent-important',
                    'due_date' => Carbon::today(),
                    'estimated_time' => '02:00',
                    'progress' => 40,
                ],
                [
                    'title' => 'Edit Video Project Client A',
                    'category' => 'creative',
                    'priority' => 'important-not-urgent',
                    'due_date' => Carbon::tomorrow(),
                    'estimated_time' => '03:00',
                    'progress' => 10,
                ],
                [
                    'title' => 'Upload Konten Instagram',
                    'category' => 'content',
                    'priority' => 'routine',
                    'due_date' => Carbon::today(),
                    'estimated_time' => '00:30',
                    'is_recurring' => true,
                    'recurring_pattern' => 'daily',
                    'progress' => 0,
                ],
            ];

            foreach ($tasks as $task) {
                Task::create(array_merge($task, [
                    'user_id' => $user->id,
                    'status' => 'todo'
                ]));
            }
        }
    }
}
