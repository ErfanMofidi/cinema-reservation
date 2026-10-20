<?php

namespace App\Services;

use App\Enums\MovieStatus;
use App\Enums\ReservationStatus;
use App\Enums\ScreeningStatus;
use App\Exceptions\BookingException;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\SeatHold;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class ReservationService
{
    /**
     * Create a confirmed demo reservation and secure its seats atomically.
     *
     * @param  list<int>  $seatIds
     */
    public function create(User $user, Screening $screening, array $seatIds, string $idempotencyKey): Reservation
    {
        $seatIds = collect($seatIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $maxSeats = max(1, (int) config('cinema.max_seats_per_reservation'));

        if ($seatIds === []) {
            throw new BookingException('Choose at least one seat before continuing.');
        }

        if (count($seatIds) > $maxSeats) {
            throw new BookingException("You can reserve up to {$maxSeats} seats at a time.");
        }

        $screening->loadMissing('movie');

        if (! $screening->isBookingOpen()) {
            throw BookingException::closed();
        }

        if ($screening->movie->status !== MovieStatus::Published) {
            throw new BookingException('Tickets for this film are not available right now.');
        }

        try {
            return DB::transaction(function () use ($user, $screening, $seatIds, $idempotencyKey) {
                $existing = Reservation::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if ($existing->user_id !== $user->id) {
                        throw new BookingException('This checkout session is no longer valid. Please start again.');
                    }

                    return $existing->load($this->detailRelations());
                }

                $lockedScreening = Screening::query()
                    ->with('movie')
                    ->lockForUpdate()
                    ->findOrFail($screening->getKey());

                if (! $lockedScreening->isBookingOpen()) {
                    throw BookingException::closed();
                }

                if ($lockedScreening->movie->status !== MovieStatus::Published) {
                    throw new BookingException('Tickets for this film are not available right now.');
                }

                $seats = Seat::query()
                    ->whereIn('id', $seatIds)
                    ->where('auditorium_id', $lockedScreening->auditorium_id)
                    ->where('is_active', true)
                    ->get()
                    ->sortBy([
                        ['row_label', 'asc'],
                        ['seat_number', 'asc'],
                    ])
                    ->values();

                if ($seats->count() !== count($seatIds)) {
                    throw new BookingException('One or more selected seats are invalid for this auditorium. Please choose again.');
                }

                $heldSeatIds = SeatHold::query()
                    ->where('screening_id', $lockedScreening->id)
                    ->whereIn('seat_id', $seatIds)
                    ->pluck('seat_id');

                if ($heldSeatIds->isNotEmpty()) {
                    $labels = $seats
                        ->whereIn('id', $heldSeatIds->all())
                        ->map(fn (Seat $seat) => $seat->label())
                        ->values()
                        ->all();

                    throw BookingException::seatsUnavailable($labels);
                }

                $subtotal = 0;
                $lineItems = [];

                foreach ($seats as $seat) {
                    $unitPrice = $lockedScreening->unitPriceFor($seat);
                    $lineItems[$seat->id] = $unitPrice;
                    $subtotal += $unitPrice;
                }

                $fees = (int) config('cinema.booking_fee', 0);

                $reservation = Reservation::create([
                    'user_id' => $user->id,
                    'screening_id' => $lockedScreening->id,
                    'reference' => $this->generateReference(),
                    'idempotency_key' => $idempotencyKey,
                    'status' => ReservationStatus::Confirmed,
                    'subtotal' => $subtotal,
                    'fees' => $fees,
                    'total' => $subtotal + $fees,
                    'currency' => config('cinema.currency'),
                    'confirmed_at' => now(),
                ]);

                foreach ($lineItems as $seatId => $unitPrice) {
                    ReservationSeat::create([
                        'reservation_id' => $reservation->id,
                        'seat_id' => $seatId,
                        'unit_price' => $unitPrice,
                    ]);
                }

                foreach ($seats as $seat) {
                    SeatHold::create([
                        'screening_id' => $lockedScreening->id,
                        'seat_id' => $seat->id,
                        'reservation_id' => $reservation->id,
                    ]);
                }

                return $reservation->load($this->detailRelations());
            });
        } catch (UniqueConstraintViolationException) {
            $existing = Reservation::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing && $existing->user_id === $user->id) {
                return $existing->load($this->detailRelations());
            }

            throw BookingException::seatsUnavailable();
        }
    }

    public function cancel(Reservation $reservation, string $reason = 'Cancelled by customer.'): Reservation
    {
        return DB::transaction(function () use ($reservation, $reason) {
            $locked = Reservation::query()
                ->with('screening')
                ->lockForUpdate()
                ->findOrFail($reservation->getKey());

            if ($locked->status === ReservationStatus::Cancelled) {
                throw new BookingException('This reservation has already been cancelled.');
            }

            if (! $locked->isCancellable()) {
                $deadline = $locked->cancellationDeadline()
                    ->timezone(config('app.timezone'))
                    ->format('D, M j \a\t H:i');

                throw new BookingException(
                    "Online cancellation closed at {$deadline}. Please contact the cinema for help."
                );
            }

            $locked->update([
                'status' => ReservationStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Holds are the active inventory, so releasing them reopens the seats.
            $locked->seatHolds()->delete();

            return $locked->load($this->detailRelations());
        });
    }

    /**
     * Cancel a screening and any confirmed reservations attached to it.
     */
    public function cancelScreening(Screening $screening): int
    {
        return DB::transaction(function () use ($screening) {
            $locked = Screening::query()
                ->lockForUpdate()
                ->findOrFail($screening->getKey());

            if ($locked->status === ScreeningStatus::Completed) {
                throw new BookingException('Completed screenings cannot be cancelled.');
            }

            if ($locked->status === ScreeningStatus::Cancelled) {
                return 0;
            }

            $reservationIds = $locked->reservations()
                ->where('status', ReservationStatus::Confirmed)
                ->pluck('id');

            if ($reservationIds->isNotEmpty()) {
                Reservation::query()
                    ->whereIn('id', $reservationIds)
                    ->update([
                        'status' => ReservationStatus::Cancelled,
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Screening cancelled by the cinema.',
                        'updated_at' => now(),
                    ]);

                SeatHold::query()
                    ->whereIn('reservation_id', $reservationIds)
                    ->delete();
            }

            $locked->update(['status' => ScreeningStatus::Cancelled]);

            return $reservationIds->count();
        });
    }

    /**
     * @return array<int, string>
     */
    private function detailRelations(): array
    {
        return [
            'screening.movie',
            'screening.auditorium',
            'reservationSeats',
            'seats',
        ];
    }

    private function generateReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $reference = 'CIN-';

            for ($character = 0; $character < 6; $character++) {
                $reference .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            if (! Reservation::query()->where('reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw new BookingException('We could not generate a booking reference. Please try again.');
    }
}
