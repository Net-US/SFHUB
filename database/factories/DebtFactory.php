<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'debtor' => $this->faker->company(),
            'type' => $this->faker->randomElement(['payable', 'receivable']),
            'amount' => $this->faker->numberBetween(1000000, 10000000),
            'interest_rate' => $this->faker->randomFloat(2, 0, 24),
            'due_date' => $this->faker->dateTimeBetween('+6 months', '+3 years'),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['active', 'pending', 'paid', 'overdue']),
            'description' => $this->faker->optional()->sentence(),
            'payment_schedule' => null,
        ];
    }
}
