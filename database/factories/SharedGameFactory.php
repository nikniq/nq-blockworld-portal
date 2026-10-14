<?php

namespace Database\Factories;

use App\Models\SharedGame;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SharedGame> */
class SharedGameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'mode' => 'survival',
            'world_size' => 'large',
            'score' => fake()->numberBetween(0, 20000),
            'nights' => fake()->numberBetween(0, 50),
            'save_path' => 'games/'.fake()->uuid().'.sav',
            'save_size' => 2_000_000,
        ];
    }
}
