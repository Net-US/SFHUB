<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PklLog>
 */
class PklLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'log_date' => now()->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'activity' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'supervisor' => $this->faker->name(),
            'skills_used' => json_encode(['PHP', 'Laravel']),
            'is_approved' => false,
        ];
    }
}
