<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\ContentSchedule;
use App\Models\Budget;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Debt;
use App\Models\Asset;
use App\Models\Transaction;
use App\Models\InvestmentInstrument;
use Carbon\Carbon;

class NewEntitiesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            return;
        }

        $this->seedEvents($user);
        $this->seedContentSchedules($user);
        $this->seedBudgets($user);
        $this->seedSubjects($user);
        $this->seedSchedules($user);
        $this->seedDebts($user);
        $this->seedAssets($user);
        $this->seedTransactions($user);
        $this->seedInvestments($user);
    }

    private function seedEvents($user)
    {
        $events = [
            ['title' => 'Seminar PKL', 'date' => now()->addDays(3), 'type' => 'seminar', 'location' => 'Auditorium'],
            ['title' => 'Deadline Proposal', 'date' => now()->addDays(7), 'type' => 'deadline'],
            ['title' => 'Rapat Tim', 'date' => now()->addDays(2), 'type' => 'acara', 'location' => 'Ruang Meeting'],
            ['title' => 'Workshop UI/UX', 'date' => now()->addDays(10), 'type' => 'seminar', 'location' => 'Lab Design'],
        ];

        foreach ($events as $event) {
            Event::create(array_merge($event, [
                'user_id' => $user->id,
                'start_time' => '09:00',
                'end_time' => '12:00',
                'notes' => null,
            ]));
        }
    }

    private function seedContentSchedules($user)
    {
        $platforms = [
            ['platform' => 'instagram', 'target_per_period' => 3, 'frequency' => 'weekly'],
            ['platform' => 'youtube', 'target_per_period' => 1, 'frequency' => 'weekly'],
            ['platform' => 'tiktok', 'target_per_period' => 5, 'frequency' => 'weekly'],
            ['platform' => 'linkedin', 'target_per_period' => 2, 'frequency' => 'weekly'],
        ];

        foreach ($platforms as $platform) {
            ContentSchedule::create(array_merge($platform, [
                'user_id' => $user->id,
                'content_type' => 'Post',
                'due_date' => now()->endOfWeek(),
                'status' => 'active',
                'completed_count' => rand(0, $platform['target_per_period']),
                'notes' => null,
            ]));
        }
    }

    private function seedBudgets($user)
    {
        $categories = [
            ['category' => 'Makan', 'amount' => 1500000, 'period' => 'monthly'],
            ['category' => 'Transport', 'amount' => 500000, 'period' => 'monthly'],
            ['category' => 'Hiburan', 'amount' => 300000, 'period' => 'monthly'],
            ['category' => 'Belanja', 'amount' => 800000, 'period' => 'monthly'],
        ];

        foreach ($categories as $budget) {
            Budget::create(array_merge($budget, [
                'user_id' => $user->id,
                'spent_amount' => rand(0, $budget['amount'] * 0.8),
                'alert_threshold' => 80,
                'is_active' => true,
            ]));
        }
    }

    private function seedSubjects($user)
    {
        $subjects = [
            ['name' => 'Pemrograman Web', 'code' => 'PW101', 'sks' => 3, 'day_of_week' => 'Senin', 'start_time' => '08:00', 'end_time' => '10:30', 'room' => 'Lab 1'],
            ['name' => 'Basis Data', 'code' => 'BD102', 'sks' => 3, 'day_of_week' => 'Selasa', 'start_time' => '13:00', 'end_time' => '15:30', 'room' => 'Lab 2'],
            ['name' => 'Jaringan Komputer', 'code' => 'JK103', 'sks' => 3, 'day_of_week' => 'Rabu', 'start_time' => '08:00', 'end_time' => '10:30', 'room' => 'Lab 3'],
            ['name' => 'Sistem Operasi', 'code' => 'SO104', 'sks' => 2, 'day_of_week' => 'Kamis', 'start_time' => '10:00', 'end_time' => '12:00', 'room' => 'Ruang 4'],
        ];

        foreach ($subjects as $subject) {
            Subject::create(array_merge($subject, [
                'user_id' => $user->id,
                'semester' => 5,
                'lecturer' => 'Dr. Example',
                'is_active' => true,
            ]));
        }
    }

    private function seedSchedules($user)
    {
        $schedules = [
            ['day_of_week' => 'Senin', 'start_time' => '07:00', 'end_time' => '08:00', 'title' => 'Morning Routine', 'activity' => 'Olahraga & Sarapan', 'type' => 'routine'],
            ['day_of_week' => 'Senin', 'start_time' => '19:00', 'end_time' => '21:00', 'title' => 'Belajar Malam', 'activity' => 'Review & Tugas', 'type' => 'academic'],
            ['day_of_week' => 'Selasa', 'start_time' => '19:00', 'end_time' => '21:00', 'title' => 'Belajar Malam', 'activity' => 'Review & Tugas', 'type' => 'academic'],
            ['day_of_week' => 'Rabu', 'start_time' => '19:00', 'end_time' => '21:00', 'title' => 'Belajar Malam', 'activity' => 'Review & Tugas', 'type' => 'academic'],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create(array_merge($schedule, [
                'user_id' => $user->id,
                'location' => 'Rumah',
                'is_recurring' => true,
                'is_active' => true,
                'priority' => 'medium',
            ]));
        }
    }

    private function seedDebts($user)
    {
        $debts = [
            ['debtor' => 'Bank ABC', 'type' => 'payable', 'amount' => 5000000, 'interest_rate' => 0, 'due_date' => now()->addMonths(6)],
            ['debtor' => 'John Doe', 'type' => 'receivable', 'amount' => 2500000, 'interest_rate' => 0, 'due_date' => now()->addDays(14)],
        ];

        foreach ($debts as $debt) {
            Debt::create(array_merge($debt, [
                'user_id' => $user->id,
                'start_date' => now(),
                'status' => 'active',
                'paid_amount' => 0,
                'remaining_amount' => $debt['amount'],
            ]));
        }
    }

    private function seedAssets($user)
    {
        $assets = [
            ['name' => 'Laptop MacBook', 'type' => 'elektronik', 'purchase_value' => 15000000, 'current_value' => 12000000, 'purchase_date' => now()->subYear()],
            ['name' => 'iPhone 14', 'type' => 'elektronik', 'purchase_value' => 12000000, 'current_value' => 9000000, 'purchase_date' => now()->subMonths(8)],
            ['name' => 'Sepeda Polygon', 'type' => 'kendaraan', 'purchase_value' => 3500000, 'current_value' => 3000000, 'purchase_date' => now()->subMonths(6)],
        ];

        foreach ($assets as $asset) {
            Asset::create(array_merge($asset, [
                'user_id' => $user->id,
                'description' => 'Asset pribadi',
                'is_insured' => false,
            ]));
        }
    }

    private function seedTransactions($user)
    {
        $transactions = [
            ['type' => 'income', 'amount' => 5000000, 'category' => 'Gaji', 'description' => 'Gaji Bulanan', 'transaction_date' => now()->subDays(5)],
            ['type' => 'expense', 'amount' => 50000, 'category' => 'Makan', 'description' => 'Sarapan', 'transaction_date' => now()->subDays(2)],
            ['type' => 'expense', 'amount' => 150000, 'category' => 'Transport', 'description' => 'GoRide', 'transaction_date' => now()->subDays(3)],
            ['type' => 'expense', 'amount' => 200000, 'category' => 'Hiburan', 'description' => 'Nonton Bioskop', 'transaction_date' => now()->subDays(1)],
        ];

        foreach ($transactions as $transaction) {
            Transaction::create(array_merge($transaction, [
                'user_id' => $user->id,
                'account_id' => 1,
                'tags' => null,
            ]));
        }
    }

    private function seedInvestments($user)
    {
        $investments = [
            ['name' => 'BBCA', 'symbol' => 'BBCA', 'type' => 'saham', 'current_price' => 8750, 'total_invested' => 10000000, 'total_quantity' => 1000, 'average_price' => 8000],
            ['name' => 'Apple Inc.', 'symbol' => 'AAPL', 'type' => 'saham_global', 'current_price' => 182.5, 'total_invested' => 15000000, 'total_quantity' => 75, 'average_price' => 170.2],
        ];

        foreach ($investments as $investment) {
            InvestmentInstrument::create(array_merge($investment, [
                'user_id' => $user->id,
            ]));
        }
    }
}
