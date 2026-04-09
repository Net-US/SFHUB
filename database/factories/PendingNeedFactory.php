<?php

namespace Database\Factories;

use App\Models\FinanceAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PendingNeed>
 */
class PendingNeedFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'finance_account_id' => FinanceAccount::factory()->state(['user_id' => $user]),
            'name' => 'Kebutuhan ' . $this->faker->word(),
            'amount' => $this->faker->numberBetween(100000, 5000000),
            'category' => $this->faker->randomElement(['Elektronik', 'Makanan', 'Transport', 'Lainnya']),
            'notes' => null,
            'status' => 'pending',
            'transaction_id' => null,
        ];
    }
}
