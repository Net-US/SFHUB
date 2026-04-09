<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinanceAccount>
 */
class FinanceAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement(['BCA', 'BNI', 'Mandiri', 'OVO']),
            'type' => $this->faker->randomElement(['cash', 'bank', 'e-wallet']),
            'account_number' => $this->faker->optional()->numerify('##########'),
            'balance' => $this->faker->numberBetween(100000, 5000000),
            'currency' => 'IDR',
            'color' => $this->faker->randomElement(['#3b82f6', '#10b981', '#f59e0b', '#ef4444']),
            'icon' => null,
            'notes' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
