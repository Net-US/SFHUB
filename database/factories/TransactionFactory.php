<?php

namespace Database\Factories;

use App\Models\FinanceAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'finance_account_id' => FinanceAccount::factory()->state(['user_id' => $user]),
            'to_account_id' => null,
            'type' => $this->faker->randomElement(['income', 'expense']),
            'amount' => $this->faker->numberBetween(50000, 1000000),
            'fee' => 0,
            'category' => $this->faker->randomElement(['Food', 'Transport', 'Salary', 'Other']),
            'description' => $this->faker->sentence(3),
            'transaction_date' => now()->toDateString(),
            'payment_method' => null,
            'notes' => null,
            'tags' => null,
            'receipt_url' => null,
            'is_recurring' => false,
            'recurring_pattern' => null,
            'related_transaction_id' => null,
        ];
    }
}
