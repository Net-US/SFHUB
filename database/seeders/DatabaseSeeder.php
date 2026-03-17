<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LandingContentSeeder::class,
            SubjectSeeder::class,
            TaskSeeder::class,
            ThesisMilestoneSeeder::class,
            PklSeeder::class,
            ScheduleSeeder::class,
            CalendarEventSeeder::class,
        ]);
    }
}
