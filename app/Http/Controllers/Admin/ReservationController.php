<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 80),
            'status' => (string) $request->query('status', ''),
            'date' => (string) $request->query('date', ''),
        ];

        if (! in_array($filters['status'], array_column(ReservationStatus::cases(), 'value'), true)) {
            $filters['status'] = '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date']) !== 1) {
            $filters['date'] = '';
        }

        $reservations = Reservation::query()
            ->with(['user', 'screening.movie', 'seats', 'reservationSeats'])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $search = '%'.$filters['q'].'%';
                $query->where(function (Builder $query) use ($search) {
                    $query->where('reference', 'like', $search)
                        ->orWhereHas('user', fn (Builder $users) => $users
                            ->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search));
                });
            })
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['date'] !== '', fn (Builder $query) => $query->whereHas(
                'screening',
                fn (Builder $screenings) => $screenings->whereDate('starts_at', $filters['date'])
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reservations.index', [
            'reservations' => $reservations,
            'filters' => $filters,
            'statuses' => ReservationStatus::cases(),
        ]);
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load([
            'user',
            'screening.movie',
            'screening.auditorium',
            'seats',
            'reservationSeats',
        ]);

        return view('admin.reservations.show', compact('reservation'));
    }
}
