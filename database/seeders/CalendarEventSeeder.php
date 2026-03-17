<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CalendarEvent;
use Carbon\Carbon;

class CalendarEventSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@sfhub.dev')->first();
        if (!$user || $user->calendarEvents()->count() > 0) return;

        $events = [
            ['title' => 'Bimbingan Skripsi',          'type' => 'academic', 'color' => '#8b5cf6', 'start_time' => now()->setDay(3)->setTime(10,0),  'end_time' => now()->setDay(3)->setTime(11,0)],
            ['title' => 'Review Konten Bulanan',       'type' => 'creative', 'color' => '#f97316', 'start_time' => now()->setDay(10)->setTime(19,0), 'end_time' => now()->setDay(10)->setTime(21,0)],
            ['title' => 'Bimbingan Skripsi Ke-2',      'type' => 'academic', 'color' => '#8b5cf6', 'start_time' => now()->setDay(15)->setTime(10,0), 'end_time' => now()->setDay(15)->setTime(11,0)],
            ['title' => 'Deadline Video Klien A',      'type' => 'creative', 'color' => '#f97316', 'start_time' => now()->setDay(18)->setTime(23,59),'end_time' => now()->setDay(18)->setTime(23,59)],
            ['title' => 'UTS Pengolahan Citra',        'type' => 'academic', 'color' => '#3b82f6', 'start_time' => now()->setDay(22)->setTime(13,0), 'end_time' => now()->setDay(22)->setTime(15,30)],
            ['title' => 'Upload Batch Shutterstock',   'type' => 'creative', 'color' => '#10b981', 'start_time' => now()->setDay(25)->setTime(19,0), 'end_time' => now()->setDay(25)->setTime(22,0)],
            ['title' => 'Laporan PKL Minggu Ini',      'type' => 'pkl',      'color' => '#14b8a6', 'start_time' => now()->setTime(16,0),             'end_time' => now()->setTime(17,0)],
        ];

        foreach ($events as $e) {
            $user->calendarEvents()->create(array_merge($e, ['user_id' => $user->id, 'description' => '']));
        }
    }
}
