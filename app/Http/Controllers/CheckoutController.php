<?php

namespace App\Http\Controllers;

use App\Enums\MovieStatus;
use App\Enums\ScreeningStatus;
use App\Exceptions\BookingException;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\SeatHold;
use App\Services\ReservationService;
use App\Support\SeatSelection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(Request $request, Screening $screening): View|RedirectResponse
    {
        $screening = Screening::query()
            ->with(['movie', 'auditorium'])
            ->findOrFail($screening->getKey());

        abort_unless($screening->status === ScreeningStatus::Scheduled, 404);
        abort_unless($screening->movie->status === MovieStatus::Published, 404);

        $seatIds = SeatSelection::parse($request->query('seats'));
        $maxSeats = max(1, (int) config('cinema.max_seats_per_reservation'));

        if ($seatIds === []) {
            return redirect()
                ->route('screenings.show', $screening)
                ->with('error', 'Choose at least one seat before continuing to checkout.');
        }

        if (count($seatIds) > $maxSeats) {
            return redirect()
                ->route('screenings.show', $screening)
                ->with('error', "You can reserve up to {$maxSeats} seats at a time.");
        }

        if (! $screening->isBookingOpen()) {
            return redirect()
                ->route('screenings.show', $screening)
                ->with('error', 'Online booking has closed for this screening. Please choose another showtime.');
        }

        $seats = Seat::query()
            ->whereIn('id', $seatIds)
            ->where('auditorium_id', $screening->auditorium_id)
            ->where('is_active', true)
            ->get()
            ->sortBy([
                ['row_label', 'asc'],
                ['seat_number', 'asc'],
            ])
            ->values();

        if ($seats->count() !== count($seatIds)) {
            return redirect()
                ->route('screenings.show', $screening)
                ->with('error', 'One or more selected seats are invalid for this auditorium. Please choose again.');
        }

        $heldSeatIds = SeatHold::query()
            ->where('screening_id', $screening->id)
            ->whereIn('seat_id', $seats->pluck('id'))
            ->pluck('seat_id');

        if ($heldSeatIds->isNotEmpty()) {
            $labels = $seats
                ->whereIn('id', $heldSeatIds->all())
                ->map(fn (Seat $seat) => $seat->label())
                ->values()
                ->all();

            return redirect()
                ->route('screenings.show', $screening)
                ->with('error', 'These seats are no longer available: '.implode(', ', $labels).'. Please choose again.');
        }

        $subtotal = $seats->sum(fn (Seat $seat) => $screening->unitPriceFor($seat));
        $fees = (int) config('cinema.booking_fee', 0);
        $total = $subtotal + $fees;

        $idempotencyKey = (string) Str::uuid();

        return view('checkout.show', compact(
            'screening',
            'seats',
            'subtotal',
            'fees',
            'total',
            'idempotencyKey',
        ));
    }

    public function store(
        StoreCheckoutRequest $request,
        Screening $screening,
        ReservationService $reservations,
    ): RedirectResponse {
        try {
            $reservation = $reservations->create(
                $request->user(),
                $screening,
                $request->validated('seats'),
                $request->validated('idempotency_key'),
            );
        } catch (BookingException $exception) {
            return redirect()
                ->route('screenings.show', [
                    'screening' => $screening,
                    'seats' => implode(',', $request->validated('seats')),
                ])
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', "Reservation {$reservation->reference} is confirmed. No payment was collected in this demo.");
    }
}
