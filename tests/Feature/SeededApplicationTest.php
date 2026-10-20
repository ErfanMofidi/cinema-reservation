<?php

use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Screening;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed();
});

it('renders the seeded public programme and seat map', function () {
    $movie = Movie::query()->where('slug', 'orbital')->firstOrFail();
    $screening = Screening::query()
        ->where('movie_id', $movie->id)
        ->where('status', 'scheduled')
        ->where('starts_at', '>', now())
        ->orderBy('starts_at')
        ->firstOrFail();

    $this->get(route('home'))->assertOk()->assertSee('Orbital');
    $this->get(route('movies.index'))->assertOk()->assertSee('Orbital');
    $this->get(route('movies.show', $movie))->assertOk()->assertSee($movie->title);
    $this->get(route('screenings.show', $screening))->assertOk()->assertSee('Choose your seats');
    $this->getJson(route('screenings.seats', $screening))->assertOk();
});

it('provides working demo customer and administrator accounts', function () {
    $customer = User::query()->where('email', 'customer@cinema.test')->firstOrFail();
    $admin = User::query()->where('email', 'admin@cinema.test')->firstOrFail();

    expect(Hash::check('Cinema123!', $customer->password))->toBeTrue()
        ->and(Hash::check('Cinema123!', $admin->password))->toBeTrue()
        ->and($customer->isAdmin())->toBeFalse()
        ->and($admin->isAdmin())->toBeTrue();

    $this->post(route('login.store'), [
        'email' => 'admin@cinema.test',
        'password' => 'Cinema123!',
    ])->assertRedirect(route('home'));

    $this->get(route('admin.dashboard'))->assertOk();
});

it('renders every seeded admin screen', function () {
    $admin = User::query()->where('email', 'admin@cinema.test')->firstOrFail();
    $movie = Movie::query()->where('slug', 'orbital')->firstOrFail();
    $screening = Screening::query()->where('movie_id', $movie->id)->firstOrFail();
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.movies.index'))->assertOk();
    $this->get(route('admin.movies.edit', $movie))->assertOk();
    $this->get(route('admin.genres.index'))->assertOk();
    $this->get(route('admin.auditoriums.index'))->assertOk();
    $this->get(route('admin.screenings.index'))->assertOk();
    $this->get(route('admin.screenings.edit', $screening))->assertOk();
    $this->get(route('admin.reservations.index'))->assertOk();
    $this->get(route('admin.reservations.show', $reservation))->assertOk();
});

it('renders the seeded customer reservation history', function () {
    $customer = User::query()->where('email', 'customer@cinema.test')->firstOrFail();
    $reservation = Reservation::query()->where('user_id', $customer->id)->firstOrFail();

    $this->actingAs($customer)
        ->get(route('reservations.index'))
        ->assertOk()
        ->assertSee($reservation->reference);

    $this->actingAs($customer)
        ->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertSee($reservation->reference);
});
