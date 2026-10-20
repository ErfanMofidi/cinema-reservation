<?php

use App\Enums\ReservationStatus;
use App\Enums\ScreeningStatus;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\SeatHold;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

/**
 * Reserve seats through the real checkout endpoint.
 *
 * @param  list<int>  $seatIds
 */
function reserveSeats($test, User $user, $screening, array $seatIds, ?string $key = null): TestResponse
{
    return $test->actingAs($user)->post(route('checkout.store', $screening), [
        'seats' => $seatIds,
        'idempotency_key' => $key ?? (string) Str::uuid(),
    ]);
}

it('requires authentication before checkout', function () {
    $screening = makeScreening();
    $seatId = seatIdsFor($screening, ['A2'])[0];

    $this->get(route('checkout.show', ['screening' => $screening, 'seats' => $seatId]))
        ->assertRedirect(route('login'));

    $this->post(route('checkout.store', $screening), [
        'seats' => [$seatId],
        'idempotency_key' => 'guest-checkout',
    ])->assertRedirect(route('login'));
});

it('shows a server-priced checkout summary', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $seatIds = seatIdsFor($screening, ['A2', 'C1']);

    $this->actingAs($user)
        ->get(route('checkout.show', [
            'screening' => $screening,
            'seats' => implode(',', $seatIds),
        ]))
        ->assertOk()
        ->assertSee('Review your reservation')
        ->assertSee('A2')
        ->assertSee('C1')
        ->assertSee('225,000 IRR')
        ->assertSee('Demo checkout');
});

it('creates a confirmed reservation with price snapshots and seat holds', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $seatIds = seatIdsFor($screening, ['A2', 'C1']);

    $response = reserveSeats($this, $user, $screening, $seatIds);

    $reservation = Reservation::query()->firstOrFail();

    $response->assertRedirect(route('reservations.show', $reservation))
        ->assertSessionHas('success');

    expect($reservation->status)->toBe(ReservationStatus::Confirmed)
        ->and($reservation->user_id)->toBe($user->id)
        ->and($reservation->screening_id)->toBe($screening->id)
        ->and($reservation->subtotal)->toBe(225_000)
        ->and($reservation->fees)->toBe(0)
        ->and($reservation->total)->toBe(225_000)
        ->and($reservation->currency)->toBe('IRR')
        ->and($reservation->reference)->toStartWith('CIN-');

    expect(ReservationSeat::query()->where('reservation_id', $reservation->id)->count())->toBe(2);
    expect(SeatHold::query()->where('reservation_id', $reservation->id)->count())->toBe(2);

    $prices = ReservationSeat::query()
        ->where('reservation_id', $reservation->id)
        ->pluck('unit_price', 'seat_id')
        ->all();

    expect($prices)->toMatchArray([
        $seatIds[0] => 100_000,
        $seatIds[1] => 125_000,
    ]);
});

it('ignores client-supplied prices, status, and user identifiers', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $seatIds = seatIdsFor($screening, ['C1']);

    $this->actingAs($user)->post(route('checkout.store', $screening), [
        'seats' => $seatIds,
        'idempotency_key' => 'tampered-price',
        'subtotal' => 1,
        'fees' => 0,
        'total' => 1,
        'price' => 1,
        'status' => ReservationStatus::Cancelled->value,
        'user_id' => 999_999,
    ])->assertRedirect();

    $reservation = Reservation::query()->firstOrFail();

    expect($reservation->user_id)->toBe($user->id)
        ->and($reservation->status)->toBe(ReservationStatus::Confirmed)
        ->and($reservation->subtotal)->toBe(125_000)
        ->and($reservation->total)->toBe(125_000);
});

it('enforces the unique screening-seat inventory constraint', function () {
    $screening = makeScreening();
    $seat = $screening->auditorium->seats()->firstOrFail();
    $reservation = Reservation::factory()->create([
        'screening_id' => $screening->id,
    ]);

    SeatHold::create([
        'screening_id' => $screening->id,
        'seat_id' => $seat->id,
        'reservation_id' => $reservation->id,
    ]);

    expect(fn () => SeatHold::create([
        'screening_id' => $screening->id,
        'seat_id' => $seat->id,
        'reservation_id' => $reservation->id,
    ]))->toThrow(QueryException::class);
});

it('generates unique reservation references', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $seatIds = seatIdsFor($screening, ['A2', 'A3']);

    reserveSeats($this, $user, $screening, [$seatIds[0]]);
    reserveSeats($this, $user, $screening, [$seatIds[1]]);

    $references = Reservation::query()->pluck('reference');

    expect($references)->toHaveCount(2)
        ->and($references->unique())->toHaveCount(2)
        ->and($references->every(fn (string $reference) => preg_match('/^CIN-[A-Z2-9]{6}$/', $reference) === 1))->toBeTrue();
});

it('rejects bookings for a cancelled screening', function () {
    $user = User::factory()->create();
    $screening = makeScreening(attributes: ['status' => ScreeningStatus::Cancelled]);
    $seatId = seatIdsFor($screening, ['A2'])[0];

    $this->actingAs($user)
        ->get(route('checkout.show', ['screening' => $screening, 'seats' => $seatId]))
        ->assertNotFound();

    reserveSeats($this, $user, $screening, [$seatId])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Reservation::query()->count())->toBe(0);
});

it('keeps a reservation idempotent when the same checkout is submitted twice', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $seatIds = seatIdsFor($screening, ['A2']);

    $key = 'checkout-key-123';

    $first = reserveSeats($this, $user, $screening, $seatIds, $key);
    $reservation = Reservation::query()->firstOrFail();
    $first->assertRedirect(route('reservations.show', $reservation));

    $second = reserveSeats($this, $user, $screening, $seatIds, $key);
    $second->assertRedirect(route('reservations.show', $reservation));

    expect(Reservation::query()->count())->toBe(1)
        ->and(SeatHold::query()->count())->toBe(1);
});

it('prevents two customers from holding the same seat', function () {
    $firstCustomer = User::factory()->create();
    $secondCustomer = User::factory()->create();
    $screening = makeScreening();
    $seatIds = seatIdsFor($screening, ['A2']);

    reserveSeats($this, $firstCustomer, $screening, $seatIds)
        ->assertRedirect();

    $response = reserveSeats($this, $secondCustomer, $screening, $seatIds);

    $response->assertRedirect()
        ->assertSessionHas('error');

    expect(Reservation::query()->count())->toBe(1)
        ->and(SeatHold::query()->where('screening_id', $screening->id)->count())->toBe(1)
        ->and(Reservation::query()->where('user_id', $secondCustomer->id)->exists())->toBeFalse();
});

it('rejects more seats than the configured maximum', function () {
    $user = User::factory()->create();
    $screening = makeScreening(attributes: ['base_price' => 100_000]);
    $seatIds = $screening->auditorium->seats()->limit(9)->pluck('id')->all();

    reserveSeats($this, $user, $screening, $seatIds)
        ->assertSessionHasErrors('seats');

    expect(Reservation::query()->count())->toBe(0);
});

it('rejects seats that belong to a different auditorium', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $foreignSeat = makeAuditorium()->seats()->firstOrFail();

    reserveSeats($this, $user, $screening, [$foreignSeat->id])
        ->assertRedirect()
        ->assertSessionHas('error', 'One or more selected seats are invalid for this auditorium. Please choose again.');

    expect(Reservation::query()->count())->toBe(0);
});

it('rejects inactive seats', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $seat = $screening->auditorium->seats()->where('row_label', 'A')->where('seat_number', 2)->firstOrFail();
    $seat->update(['is_active' => false]);

    reserveSeats($this, $user, $screening, [$seat->id])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Reservation::query()->count())->toBe(0);
});

it('does not allow checkout after the booking cutoff', function () {
    $user = User::factory()->create();
    $screening = makeScreening(attributes: [
        'starts_at' => now()->addMinutes(10),
        'ends_at' => now()->addMinutes(130),
    ]);
    $seatId = seatIdsFor($screening, ['A2'])[0];

    $this->actingAs($user)
        ->get(route('checkout.show', ['screening' => $screening, 'seats' => $seatId]))
        ->assertRedirect(route('screenings.show', $screening))
        ->assertSessionHas('error');

    reserveSeats($this, $user, $screening, [$seatId])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Reservation::query()->count())->toBe(0);
});

it('cancels a reservation and releases its seats', function () {
    $user = User::factory()->create();
    $screening = makeScreening();
    $seatIds = seatIdsFor($screening, ['A2', 'A3']);

    reserveSeats($this, $user, $screening, $seatIds);
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($user)
        ->post(route('reservations.cancel', $reservation))
        ->assertRedirect(route('reservations.show', $reservation))
        ->assertSessionHas('success');

    $reservation->refresh();

    expect($reservation->status)->toBe(ReservationStatus::Cancelled)
        ->and($reservation->cancelled_at)->not->toBeNull()
        ->and($reservation->cancellation_reason)->toContain('Cancelled by customer')
        ->and(SeatHold::query()->where('reservation_id', $reservation->id)->count())->toBe(0)
        ->and(ReservationSeat::query()->where('reservation_id', $reservation->id)->count())->toBe(2);

    $available = collect($this->getJson(route('screenings.seats', $screening))->json('data.seats'))
        ->whereIn('id', $seatIds);

    expect($available->every(fn (array $seat) => $seat['available'] === true))->toBeTrue();
});

it('refuses to cancel a reservation twice', function () {
    $user = User::factory()->create();
    $screening = makeScreening();

    reserveSeats($this, $user, $screening, seatIdsFor($screening, ['A2']));
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($user)
        ->post(route('reservations.cancel', $reservation))
        ->assertSessionHas('success');

    $this->actingAs($user)
        ->post(route('reservations.cancel', $reservation))
        ->assertSessionHas('error');

    expect($reservation->refresh()->status)->toBe(ReservationStatus::Cancelled);
});

it('refuses online cancellation inside the cancellation cutoff', function () {
    $user = User::factory()->create();
    $screening = makeScreening(attributes: [
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addHours(3),
    ]);

    reserveSeats($this, $user, $screening, seatIdsFor($screening, ['A2']));
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($user)
        ->post(route('reservations.cancel', $reservation))
        ->assertRedirect(route('reservations.show', $reservation))
        ->assertSessionHas('error');

    expect($reservation->refresh()->status)->toBe(ReservationStatus::Confirmed)
        ->and(SeatHold::query()->where('reservation_id', $reservation->id)->count())->toBe(1);
});

it('prevents another customer from viewing or cancelling a reservation', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $screening = makeScreening();

    reserveSeats($this, $owner, $screening, seatIdsFor($screening, ['A2']));
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($intruder)
        ->get(route('reservations.show', $reservation))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->post(route('reservations.cancel', $reservation))
        ->assertForbidden();

    expect($reservation->refresh()->status)->toBe(ReservationStatus::Confirmed);
});

it('allows an administrator to view a reservation but not cancel it as the customer', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $screening = makeScreening();

    reserveSeats($this, $owner, $screening, seatIdsFor($screening, ['A2']));
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($admin)
        ->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertSee($reservation->reference)
        ->assertSee($owner->email);

    $this->actingAs($admin)
        ->post(route('reservations.cancel', $reservation))
        ->assertForbidden();
});

it('lists a customer reservation history', function () {
    $owner = User::factory()->create();
    $screening = makeScreening();

    reserveSeats($this, $owner, $screening, seatIdsFor($screening, ['A2']));
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($owner)
        ->get(route('reservations.index'))
        ->assertOk()
        ->assertSee($reservation->reference)
        ->assertSee($screening->movie->title);
});

it('cancels confirmed reservations when an administrator cancels the screening', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $screening = makeScreening();

    reserveSeats($this, $owner, $screening, seatIdsFor($screening, ['A2']));
    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.screenings.cancel', $screening))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($screening->refresh()->status)->toBe(ScreeningStatus::Cancelled)
        ->and($reservation->refresh()->status)->toBe(ReservationStatus::Cancelled)
        ->and($reservation->cancellation_reason)->toBe('Screening cancelled by the cinema.')
        ->and(SeatHold::query()->where('reservation_id', $reservation->id)->count())->toBe(0);
});
