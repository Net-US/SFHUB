<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => $this->faker->randomElement(['Makanan', 'Transport', 'Hiburan', 'Belanja']),
            'amount' => $this->faker->numberBetween(500000, 5000000),
            'period' => $this->faker->randomElement(['monthly', 'weekly']),
            'spent_amount' => 0,
            'alert_threshold' => 80,
            'is_active' => true,
        ];
    }
}
