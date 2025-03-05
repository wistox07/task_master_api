<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "title" => fake()->sentence(3),
            "description" => fake()->paragraph(3),
            "expiration_date" =>  fake()->dateTimeBetween('+10 days', '+1 month')->format('Y-m-d'),
        ];
    }
}
