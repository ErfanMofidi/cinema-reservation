<?php

use App\Enums\MovieStatus;
use App\Enums\ReservationStatus;
use App\Enums\ScreeningStatus;
use App\Models\Auditorium;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Screening;
use App\Models\SeatHold;
use App\Models\User;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function moviePayload(Genre $genre, array $overrides = []): array
{
    return [
        'title' => 'Test Film',
        'slug' => 'test-film',
        'tagline' => 'A small film with a large heart.',
        'synopsis' => 'A carefully written synopsis that comfortably exceeds the minimum length required by validation.',
        'poster_path' => '',
        'backdrop_path' => '',
        'runtime_minutes' => 112,
        'age_rating' => 'PG-13',
        'language' => 'English',
        'release_date' => now()->subDays(3)->toDateString(),
        'status' => MovieStatus::Published->value,
        'genres' => [$genre->id],
        ...$overrides,
    ];
}

it('renders every admin index and creation screen', function () {
    $admin = User::factory()->admin()->create();
    $auditorium = makeAuditorium();
    makeScreening($auditorium);

    $routes = [
        'admin.dashboard',
        'admin.movies.index',
        'admin.movies.create',
        'admin.genres.index',
        'admin.genres.create',
        'admin.auditoriums.index',
        'admin.auditoriums.create',
        'admin.screenings.index',
        'admin.screenings.create',
        'admin.reservations.index',
    ];

    foreach ($routes as $route) {
        $this->actingAs($admin)->get(route($route))->assertOk();
    }
});

it('creates and updates a film with genres', function () {
    $admin = User::factory()->admin()->create();
    $genre = Genre::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.movies.store'), moviePayload($genre))
        ->assertRedirect(route('admin.movies.index'))
        ->assertSessionHas('success');

    $movie = Movie::query()->where('slug', 'test-film')->firstOrFail();

    expect($movie->title)->toBe('Test Film')
        ->and($movie->status)->toBe(MovieStatus::Published)
        ->and($movie->genres->pluck('id')->all())->toBe([$genre->id]);

    $this->actingAs($admin)
        ->patch(route('admin.movies.update', $movie), moviePayload($genre, [
            'title' => 'Updated Film',
            'slug' => 'updated-film',
            'runtime_minutes' => 98,
        ]))
        ->assertRedirect(route('admin.movies.edit', ['movie' => 'updated-film']));

    $movie->refresh();

    expect($movie->title)->toBe('Updated Film')
        ->and($movie->slug)->toBe('updated-film')
        ->and($movie->runtime_minutes)->toBe(98);
});

it('validates film payloads', function () {
    $admin = User::factory()->admin()->create();
    $genre = Genre::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.movies.store'), moviePayload($genre, [
            'title' => '',
            'slug' => 'Not A Slug',
            'synopsis' => 'Too short.',
            'runtime_minutes' => 0,
            'genres' => [],
        ]))
        ->assertSessionHasErrors(['title', 'slug', 'synopsis', 'runtime_minutes', 'genres']);

    expect(Movie::query()->count())->toBe(0);
});

it('archives a film only when it has no upcoming screenings', function () {
    $admin = User::factory()->admin()->create();
    $movie = Movie::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.movies.archive', $movie))
        ->assertRedirect(route('admin.movies.index'))
        ->assertSessionHas('success');

    expect($movie->refresh()->status)->toBe(MovieStatus::Archived);

    $busyMovie = Movie::factory()->create();
    makeScreening(movie: $busyMovie);

    $this->actingAs($admin)
        ->patch(route('admin.movies.archive', $busyMovie))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($busyMovie->refresh()->status)->toBe(MovieStatus::Published);
});

it('refuses to archive a film through the edit form while screenings are upcoming', function () {
    $admin = User::factory()->admin()->create();
    $genre = Genre::factory()->create();
    $movie = Movie::factory()->create();
    $movie->genres()->attach($genre);
    makeScreening(movie: $movie);

    $this->actingAs($admin)
        ->from(route('admin.movies.edit', $movie))
        ->patch(route('admin.movies.update', $movie), moviePayload($genre, [
            'title' => $movie->title,
            'slug' => $movie->slug,
            'status' => MovieStatus::Archived->value,
        ]))
        ->assertRedirect(route('admin.movies.edit', $movie))
        ->assertSessionHasErrors('status');

    expect($movie->refresh()->status)->toBe(MovieStatus::Published);
});

it('manages genres and refuses to delete an assigned genre', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.genres.store'), [
            'name' => 'Mystery',
            'slug' => 'mystery',
        ])
        ->assertRedirect(route('admin.genres.index'));

    $genre = Genre::query()->where('slug', 'mystery')->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.genres.update', $genre), [
            'name' => 'Mystery & Suspense',
            'slug' => 'mystery-suspense',
        ])
        ->assertRedirect(route('admin.genres.index'));

    expect($genre->refresh()->name)->toBe('Mystery & Suspense');

    $movie = Movie::factory()->create();
    $movie->genres()->attach($genre);

    $this->actingAs($admin)
        ->delete(route('admin.genres.destroy', $genre))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Genre::query()->whereKey($genre->id)->exists())->toBeTrue();

    $movie->genres()->detach($genre);

    $this->actingAs($admin)
        ->delete(route('admin.genres.destroy', $genre))
        ->assertRedirect(route('admin.genres.index'));

    expect(Genre::query()->whereKey($genre->id)->exists())->toBeFalse();
});

it('creates an auditorium with a generated seat layout and updates its details', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.auditoriums.store'), [
            'name' => 'Studio One',
            'code' => 'STUDIO-1',
            'screen_label' => 'Studio Screen',
            'is_active' => '1',
            'rows' => 2,
            'seats_per_row' => 3,
            'premium_rows' => 1,
        ])
        ->assertRedirect();

    $auditorium = Auditorium::query()->where('code', 'STUDIO-1')->firstOrFail();

    expect($auditorium->capacity)->toBe(6)
        ->and($auditorium->seats()->count())->toBe(6)
        ->and($auditorium->seats()->where('seat_type', 'premium')->count())->toBe(3)
        ->and($auditorium->seats()->where('seat_type', 'accessible')->count())->toBe(2);

    $this->actingAs($admin)
        ->get(route('admin.auditoriums.edit', $auditorium))
        ->assertOk()
        ->assertSee('Studio One');

    $this->actingAs($admin)
        ->patch(route('admin.auditoriums.update', $auditorium), [
            'name' => 'Studio Two',
            'code' => 'STUDIO-2',
            'screen_label' => 'Main Studio',
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.auditoriums.edit', $auditorium));

    $auditorium->refresh();

    expect($auditorium->name)->toBe('Studio Two')
        ->and($auditorium->code)->toBe('STUDIO-2')
        ->and($auditorium->is_active)->toBeFalse();
});

it('rebuilds a seat layout only while the auditorium has no screenings', function () {
    $admin = User::factory()->admin()->create();
    $auditorium = makeAuditorium(rows: 2, seatsPerRow: 3, premiumRows: 1);

    $this->actingAs($admin)
        ->post(route('admin.auditoriums.layout', $auditorium), [
            'rows' => 3,
            'seats_per_row' => 4,
            'premium_rows' => 1,
        ])
        ->assertRedirect(route('admin.auditoriums.edit', $auditorium));

    expect($auditorium->refresh()->capacity)->toBe(12)
        ->and($auditorium->seats()->count())->toBe(12);

    makeScreening($auditorium);

    $this->actingAs($admin)
        ->post(route('admin.auditoriums.layout', $auditorium), [
            'rows' => 2,
            'seats_per_row' => 2,
            'premium_rows' => 0,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($auditorium->refresh()->capacity)->toBe(12);
});

it('schedules, updates, and deletes a screening when safe', function () {
    $admin = User::factory()->admin()->create();
    $movie = Movie::factory()->create();
    $auditorium = makeAuditorium();

    $startsAt = now()->addDays(4)->setTime(14, 0);

    $this->actingAs($admin)
        ->post(route('admin.screenings.store'), [
            'movie_id' => $movie->id,
            'auditorium_id' => $auditorium->id,
            'starts_at' => $startsAt->format('Y-m-d\TH:i'),
            'ends_at' => $startsAt->copy()->addMinutes(120)->format('Y-m-d\TH:i'),
            'base_price' => 150_000,
        ])
        ->assertRedirect(route('admin.screenings.index'))
        ->assertSessionHas('success');

    $screening = Screening::query()->where('movie_id', $movie->id)->firstOrFail();

    expect($screening->status)->toBe(ScreeningStatus::Scheduled)
        ->and($screening->base_price)->toBe(150_000);

    $newStart = now()->addDays(5)->setTime(16, 0);

    $this->actingAs($admin)
        ->patch(route('admin.screenings.update', $screening), [
            'movie_id' => $movie->id,
            'auditorium_id' => $auditorium->id,
            'starts_at' => $newStart->format('Y-m-d\TH:i'),
            'ends_at' => $newStart->copy()->addMinutes(100)->format('Y-m-d\TH:i'),
            'base_price' => 175_000,
        ])
        ->assertRedirect(route('admin.screenings.index'));

    $screening->refresh();

    expect($screening->base_price)->toBe(175_000)
        ->and($screening->starts_at->format('Y-m-d H:i'))->toBe($newStart->format('Y-m-d H:i'));

    $this->actingAs($admin)
        ->delete(route('admin.screenings.destroy', $screening))
        ->assertRedirect(route('admin.screenings.index'));

    expect(Screening::query()->whereKey($screening->id)->exists())->toBeFalse();
});

it('rejects overlapping screenings inside the auditorium buffer', function () {
    $admin = User::factory()->admin()->create();
    $movie = Movie::factory()->create();
    $auditorium = makeAuditorium();
    $startsAt = now()->addDays(4)->setTime(18, 0);

    $existing = makeScreening($auditorium, $movie, [
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(120),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.screenings.create'))
        ->post(route('admin.screenings.store'), [
            'movie_id' => $movie->id,
            'auditorium_id' => $auditorium->id,
            'starts_at' => $startsAt->copy()->addMinutes(60)->format('Y-m-d\TH:i'),
            'ends_at' => $startsAt->copy()->addMinutes(180)->format('Y-m-d\TH:i'),
            'base_price' => 150_000,
        ])
        ->assertRedirect(route('admin.screenings.create'))
        ->assertSessionHasErrors('starts_at');

    expect(Screening::query()->count())->toBe(1);
});

it('allows back-to-back screenings with the configured buffer', function () {
    $admin = User::factory()->admin()->create();
    $movie = Movie::factory()->create();
    $auditorium = makeAuditorium();
    $startsAt = now()->addDays(4)->setTime(18, 0);

    makeScreening($auditorium, $movie, [
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(120),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.screenings.store'), [
            'movie_id' => $movie->id,
            'auditorium_id' => $auditorium->id,
            'starts_at' => $startsAt->copy()->addMinutes(135)->format('Y-m-d\TH:i'),
            'ends_at' => $startsAt->copy()->addMinutes(255)->format('Y-m-d\TH:i'),
            'base_price' => 150_000,
        ])
        ->assertRedirect(route('admin.screenings.index'));

    expect(Screening::query()->count())->toBe(2);
});

it('shows an archived film on the screening edit form', function () {
    $admin = User::factory()->admin()->create();
    $movie = Movie::factory()->archived()->create();
    $screening = makeScreening(movie: $movie);

    $this->actingAs($admin)
        ->get(route('admin.screenings.edit', $screening))
        ->assertOk()
        ->assertSee($movie->title);
});

it('cancels a screening with reservations and retains the booking record', function () {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();
    $screening = makeScreening();
    $seatId = $screening->auditorium->seats()->firstOrFail()->id;

    $this->actingAs($owner)->post(route('checkout.store', $screening), [
        'seats' => [$seatId],
        'idempotency_key' => 'admin-cancel-test',
    ]);

    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.screenings.cancel', $screening))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($screening->refresh()->status)->toBe(ScreeningStatus::Cancelled)
        ->and($reservation->refresh()->status)->toBe(ReservationStatus::Cancelled)
        ->and(SeatHold::query()->where('reservation_id', $reservation->id)->exists())->toBeFalse();

    $this->actingAs($admin)
        ->get(route('admin.reservations.show', $reservation))
        ->assertOk()
        ->assertSee('The underlying screening was cancelled.');
});

it('does not delete a screening with reservation history', function () {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();
    $screening = makeScreening();
    $seatId = $screening->auditorium->seats()->firstOrFail()->id;

    $this->actingAs($owner)->post(route('checkout.store', $screening), [
        'seats' => [$seatId],
        'idempotency_key' => 'admin-delete-test',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.screenings.destroy', $screening))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Screening::query()->whereKey($screening->id)->exists())->toBeTrue();
});

it('shows reservation records to administrators', function () {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();
    $screening = makeScreening();
    $seatId = $screening->auditorium->seats()->firstOrFail()->id;

    $this->actingAs($owner)->post(route('checkout.store', $screening), [
        'seats' => [$seatId],
        'idempotency_key' => 'admin-reservation-test',
    ]);

    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.reservations.index'))
        ->assertOk()
        ->assertSee($reservation->reference)
        ->assertSee($owner->email);

    $this->actingAs($admin)
        ->get(route('admin.reservations.show', $reservation))
        ->assertOk()
        ->assertSee($reservation->reference)
        ->assertSee($owner->name);
});

it('blocks non-admins from every admin route group', function () {
    $customer = User::factory()->create();
    $genre = Genre::factory()->create();
    $movie = Movie::factory()->create();
    $auditorium = makeAuditorium();
    $screening = makeScreening($auditorium);
    $reservation = Reservation::factory()->create([
        'user_id' => $customer->id,
        'screening_id' => $screening->id,
    ]);

    $requests = [
        ['get', route('admin.movies.index')],
        ['post', route('admin.movies.store')],
        ['get', route('admin.genres.index')],
        ['get', route('admin.auditoriums.index')],
        ['get', route('admin.screenings.index')],
        ['get', route('admin.reservations.index')],
        ['get', route('admin.reservations.show', $reservation)],
    ];

    foreach ($requests as [$method, $url]) {
        $this->actingAs($customer)->{$method}($url)->assertForbidden();
    }
});
