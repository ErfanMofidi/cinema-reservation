<?php

namespace App\Http\Controllers;

use App\Enums\MovieStatus;
use App\Enums\ScreeningStatus;
use App\Models\Screening;
use App\Models\Seat;
use App\Support\CinemaFormat;
use App\Support\SeatSelection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function show(Request $request, Screening $screening): View
    {
        $screening = Screening::query()
            ->with(['movie.genres', 'auditorium.seats', 'seatHolds'])
            ->findOrFail($screening->getKey());

        abort_unless($screening->status === ScreeningStatus::Scheduled, 404);
        abort_unless($screening->movie->status === MovieStatus::Published, 404);

        $seats = $screening->auditorium->seats
            ->where('is_active', true)
            ->values();

        $heldIds = $screening->seatHolds->pluck('seat_id')->all();
        $heldLookup = array_fill_keys($heldIds, true);
        $selectedSeatIds = SeatSelection::parse($request->query('seats'));

        $rows = $seats->groupBy('row_label')->sortKeys();
        $maxColumns = max(1, (int) ($seats->max('seat_number') ?? 1));
        $remaining = max(0, $seats->count() - $seats->whereIn('id', $heldIds)->count());
        $bookable = $screening->isBookingOpen();

        $otherScreenings = Screening::query()
            ->where('movie_id', $screening->movie_id)
            ->whereKeyNot($screening->getKey())
            ->where('starts_at', '>', now())
            ->scheduled()
            ->with('auditorium')
            ->orderBy('starts_at')
            ->limit(4)
            ->get();

        return view('screenings.show', compact(
            'screening',
            'seats',
            'rows',
            'maxColumns',
            'heldLookup',
            'selectedSeatIds',
            'remaining',
            'bookable',
            'otherScreenings',
        ));
    }

    public function seats(Screening $screening): JsonResponse
    {
        $screening = Screening::query()
            ->with(['movie', 'auditorium.seats', 'seatHolds'])
            ->findOrFail($screening->getKey());

        abort_unless($screening->status === ScreeningStatus::Scheduled, 404);
        abort_unless($screening->movie->status === MovieStatus::Published, 404);

        $held = $screening->seatHolds->pluck('seat_id')->flip();
        $maxSeats = (int) config('cinema.max_seats_per_reservation');

        $seats = $screening->auditorium->seats
            ->where('is_active', true)
            ->map(fn (Seat $seat) => [
                'id' => $seat->id,
                'row_label' => $seat->row_label,
                'seat_number' => $seat->seat_number,
                'label' => $seat->label(),
                'seat_type' => $seat->seat_type->value,
                'seat_type_label' => $seat->seat_type->label(),
                'price' => $screening->unitPriceFor($seat),
                'formatted_price' => CinemaFormat::money($screening->unitPriceFor($seat)),
                'available' => ! $held->has($seat->id),
            ])
            ->values();

        return response()->json([
            'data' => [
                'screening_id' => $screening->id,
                'booking_open' => $screening->isBookingOpen(),
                'max_seats' => $maxSeats,
                'seats' => $seats,
            ],
        ])->withHeaders([
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
