<?php

namespace Database\Factories;

use App\Enums\MovieStatus;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'tagline' => fake()->sentence(6),
            'synopsis' => fake()->paragraphs(2, true),
            'poster_path' => null,
            'backdrop_path' => null,
            'runtime_minutes' => fake()->numberBetween(88, 145),
            'age_rating' => fake()->randomElement(['PG', 'PG-13', 'R', '12+']),
            'language' => 'English',
            'release_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'status' => MovieStatus::Published,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => MovieStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => MovieStatus::Archived]);
    }

    public function comingSoon(): static
    {
        return $this->state(fn () => ['release_date' => now()->addDays(21)]);
    }
}
