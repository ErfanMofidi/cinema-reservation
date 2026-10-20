<?php

namespace Database\Factories;

use App\Enums\ScreeningStatus;
use App\Models\Auditorium;
use App\Models\Movie;
use App\Models\Screening;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Screening>
 */
class ScreeningFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = now()->addDays(2)->setTime(18, 0);

        return [
            'movie_id' => Movie::factory(),
            'auditorium_id' => Auditorium::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(120),
            'base_price' => 350000,
            'status' => ScreeningStatus::Scheduled,
        ];
    }

    public function past(): static
    {
        return $this->state(function () {
            $startsAt = now()->subDays(3)->setTime(18, 0);

            return [
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes(120),
                'status' => ScreeningStatus::Completed,
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ScreeningStatus::Cancelled]);
    }
}
