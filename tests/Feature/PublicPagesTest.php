<?php

use App\Enums\ScreeningStatus;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\SeatHold;

it('renders the home page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(config('cinema.name'))
        ->assertSee('Now showing');
});

it('renders the public programme with published films only', function () {
    $published = Movie::factory()->create(['title' => 'Northern Light']);
    $draft = Movie::factory()->draft()->create(['title' => 'Hidden Draft']);

    $this->get(route('movies.index'))
        ->assertOk()
        ->assertSee('Northern Light')
        ->assertDontSee('Hidden Draft');

    $this->get(route('movies.index', ['q' => 'Northern']))
        ->assertOk()
        ->assertSee('Northern Light')
        ->assertDontSee('Hidden Draft');
});

it('filters the programme by genre and release window', function () {
    $genre = Genre::factory()->create(['name' => 'Science Fiction', 'slug' => 'science-fiction']);
    $matching = Movie::factory()->create(['title' => 'Orbit']);
    $matching->genres()->attach($genre);
    $upcoming = Movie::factory()->comingSoon()->create(['title' => 'Future Film']);
    $upcoming->genres()->attach($genre);

    $this->get(route('movies.index', ['genre' => 'science-fiction']))
        ->assertOk()
        ->assertSee('Orbit')
        ->assertSee('Future Film');

    $this->get(route('movies.index', ['genre' => 'science-fiction', 'showing' => 'coming']))
        ->assertOk()
        ->assertSee('Future Film')
        ->assertDontSee('Orbit');
});

it('renders a published film with genres and scheduled screenings', function () {
    $genre = Genre::factory()->create(['name' => 'Slow Cinema', 'slug' => 'slow-cinema']);
    $movie = Movie::factory()->create(['title' => 'The Long Take']);
    $movie->genres()->attach($genre);
    $screening = makeScreening(movie: $movie);

    $this->get(route('movies.show', $movie))
        ->assertOk()
        ->assertSee('The Long Take')
        ->assertSee('Slow Cinema')
        ->assertSee($screening->auditorium->name)
        ->assertSee($screening->starts_at->format('H:i'));
});

it('handles empty search results and pagination', function () {
    Movie::factory()->count(13)->create();

    $firstPage = $this->get(route('movies.index'))->assertOk();
    $secondPage = $this->get(route('movies.index', ['page' => 2]))->assertOk();

    expect($firstPage->viewData('movies')->total())->toBe(13)
        ->and($firstPage->viewData('movies')->count())->toBe(12)
        ->and($secondPage->viewData('movies')->count())->toBe(1);

    $this->get(route('movies.index', ['q' => 'no-film-matches-this']))
        ->assertOk()
        ->assertSee('No films match those filters');
});

it('renders the custom not-found page', function () {
    $this->get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSee('این صفحه مفقود است');
});

it('hides draft and archived films from the public film page', function () {
    $draft = Movie::factory()->draft()->create();
    $archived = Movie::factory()->archived()->create();

    $this->get(route('movies.show', $draft))->assertNotFound();
    $this->get(route('movies.show', $archived))->assertNotFound();
});

it('renders the seat map with prices and unavailable seats', function () {
    $screening = makeScreening();
    $heldSeat = $screening->auditorium->seats()->where('row_label', 'A')->where('seat_number', 2)->firstOrFail();

    SeatHold::factory()->create([
        'screening_id' => $screening->id,
        'seat_id' => $heldSeat->id,
    ]);

    $this->get(route('screenings.show', $screening))
        ->assertOk()
        ->assertSee('Choose your seats')
        ->assertSee($screening->movie->title)
        ->assertSee($heldSeat->accessibleName())
        ->assertSee('unavailable');
});

it('returns live seat availability as JSON', function () {
    $screening = makeScreening();
    $heldSeat = $screening->auditorium->seats()->where('row_label', 'B')->where('seat_number', 1)->firstOrFail();

    SeatHold::factory()->create([
        'screening_id' => $screening->id,
        'seat_id' => $heldSeat->id,
    ]);

    $response = $this->getJson(route('screenings.seats', $screening))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('data.screening_id', $screening->id)
        ->assertJsonPath('data.booking_open', true)
        ->assertJsonPath('data.max_seats', 8);

    $seats = collect($response->json('data.seats'));

    expect($seats)->toHaveCount(12)
        ->and($seats->firstWhere('id', $heldSeat->id)['available'])->toBeFalse()
        ->and($seats->where('available', true))->toHaveCount(11);
});

it('does not expose seat availability for cancelled screenings', function () {
    $screening = makeScreening(attributes: ['status' => ScreeningStatus::Cancelled]);

    $this->get(route('screenings.show', $screening))->assertNotFound();
    $this->getJson(route('screenings.seats', $screening))->assertNotFound();
});

it('does not expose screenings for unpublished films', function () {
    $movie = Movie::factory()->draft()->create();
    $screening = makeScreening(movie: $movie);

    $this->get(route('screenings.show', $screening))->assertNotFound();
    $this->getJson(route('screenings.seats', $screening))->assertNotFound();
});

it('shows the booking-closed state on a screening inside the cutoff', function () {
    $screening = makeScreening(attributes: [
        'starts_at' => now()->addMinutes(10),
        'ends_at' => now()->addMinutes(130),
    ]);

    $this->get(route('screenings.show', $screening))
        ->assertOk()
        ->assertSee('Online booking is closed for this screening.');
});

it('adds baseline security headers to responses', function () {
    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});
