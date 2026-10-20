<?php

namespace Database\Factories;

use App\Enums\SeatType;
use App\Models\Auditorium;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seat>
 */
class SeatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'auditorium_id' => Auditorium::factory(),
            'row_label' => fake()->randomElement(range('A', 'H')),
            'seat_number' => fake()->unique()->numberBetween(1, 30),
            'seat_type' => SeatType::Standard,
            'is_active' => true,
        ];
    }

    public function premium(): static
    {
        return $this->state(fn () => ['seat_type' => SeatType::Premium]);
    }

    public function accessible(): static
    {
        return $this->state(fn () => ['seat_type' => SeatType::Accessible]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
