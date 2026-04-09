<?php

namespace Database\Factories;

use App\Models\FinanceAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavingsGoal>
 */
class SavingsGoalFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'finance_account_id' => FinanceAccount::factory()->state(['user_id' => $user]),
            'name' => 'Tabungan ' . $this->faker->word(),
            'target_amount' => $this->faker->numberBetween(1000000, 20000000),
            'current_amount' => $this->faker->numberBetween(0, 5000000),
            'daily_saving' => $this->faker->numberBetween(10000, 200000),
            'start_date' => now()->toDateString(),
            'target_date' => now()->addMonths(6)->toDateString(),
            'notes' => null,
            'status' => 'active',
        ];
    }
}
