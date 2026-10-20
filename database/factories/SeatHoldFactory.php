<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\SeatHold;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeatHold>
 */
class SeatHoldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'screening_id' => Screening::factory(),
            'seat_id' => Seat::factory(),
            'reservation_id' => Reservation::factory(),
        ];
    }
}
