<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\ScreeningStatus;
use App\Exceptions\BookingException;
use App\Models\Reservation;
use App\Models\Screening;
use App\Services\ReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $reservations = $request->user()
            ->reservations()
            ->with([
                'screening.movie',
                'screening.auditorium',
                'seats',
                'reservationSeats',
            ])
            ->orderByDesc(
                Screening::query()
                    ->select('starts_at')
                    ->whereColumn('screenings.id', 'reservations.screening_id')
            )
            ->paginate(8);

        $upcoming = $reservations->getCollection()
            ->filter(fn (Reservation $reservation) => $reservation->status === ReservationStatus::Confirmed
                && $reservation->screening->status === ScreeningStatus::Scheduled
                && $reservation->screening->starts_at->isFuture());

        $history = $reservations->getCollection()->diffKeys($upcoming);

        return view('reservations.index', compact('reservations', 'upcoming', 'history'));
    }

    public function show(Reservation $reservation): View
    {
        Gate::authorize('view', $reservation);

        $reservation->load([
            'user',
            'screening.movie',
            'screening.auditorium',
            'seats',
            'reservationSeats',
        ]);

        return view('reservations.show', compact('reservation'));
    }

    public function cancel(
        Request $request,
        Reservation $reservation,
        ReservationService $reservations,
    ): RedirectResponse {
        Gate::authorize('view', $reservation);

        abort_unless($reservation->user_id === $request->user()->id, 403, 'Only the ticket holder can cancel this reservation.');

        try {
            $reservations->cancel($reservation);
        } catch (BookingException $exception) {
            return redirect()
                ->route('reservations.show', $reservation)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Your reservation was cancelled and the seats were released.');
    }
}
