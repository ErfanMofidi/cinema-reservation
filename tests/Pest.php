<?php

use App\Models\Auditorium;
use App\Models\Movie;
use App\Models\Screening;
use App\Services\SeatLayoutGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create an auditorium with a deterministic rectangular seat layout.
 */
function makeAuditorium(int $rows = 3, int $seatsPerRow = 4, int $premiumRows = 1): Auditorium
{
    $auditorium = Auditorium::factory()->create(['capacity' => 0]);

    app(SeatLayoutGenerator::class)->generate($auditorium, $rows, $seatsPerRow, $premiumRows);

    return $auditorium->refresh();
}

/**
 * Create a published film with a bookable screening two days from now.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeScreening(?Auditorium $auditorium = null, ?Movie $movie = null, array $attributes = []): Screening
{
    $auditorium ??= makeAuditorium();
    $movie ??= Movie::factory()->create();

    $startsAt = $attributes['starts_at'] ?? now()->addDays(2)->setTime(18, 0);
    $endsAt = $attributes['ends_at'] ?? $startsAt->copy()->addMinutes($movie->runtime_minutes);

    return Screening::factory()->create([
        'movie_id' => $movie->id,
        'auditorium_id' => $auditorium->id,
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'base_price' => 100_000,
        ...$attributes,
    ]);
}

/**
 * Return the IDs of the requested seat labels (for example A1, B3).
 *
 * @param  list<string>  $labels
 * @return list<int>
 */
function seatIdsFor(Screening $screening, array $labels): array
{
    $seats = $screening->auditorium->seats()
        ->get()
        ->filter(fn ($seat) => in_array($seat->label(), $labels, true));

    return $seats->pluck('id')->values()->all();
}
