<?php

namespace Database\Factories;

use App\Models\Auditorium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auditorium>
 */
class AuditoriumFactory extends Factory
{
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 999);

        return [
            'name' => "Auditorium {$number}",
            'code' => "AUD-{$number}",
            'screen_label' => "Screen {$number}",
            'capacity' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
