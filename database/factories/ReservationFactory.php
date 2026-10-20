<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Screening;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1, 4) * 350000;

        return [
            'user_id' => User::factory(),
            'screening_id' => Screening::factory(),
            'reference' => fake()->unique()->bothify('CIN-######'),
            'idempotency_key' => fake()->uuid(),
            'status' => ReservationStatus::Confirmed,
            'subtotal' => $subtotal,
            'fees' => 0,
            'total' => $subtotal,
            'currency' => config('cinema.currency', 'IRR'),
            'confirmed_at' => now(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelled by customer.',
        ]);
    }
}
