<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDays(random_int(1, 30));
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(6),
            'venue' => fake()->city(),
            'start' => $start,
            'end' => $start->addHours(random_int(1, 72)),
        ];
    }
}
