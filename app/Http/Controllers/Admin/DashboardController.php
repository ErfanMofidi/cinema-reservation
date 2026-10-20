<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\Screening;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $publishedMovies = Movie::query()->published()->count();
        $upcomingScreenings = Screening::query()->upcoming()->count();
        $confirmedReservations = Reservation::query()->confirmed()->count();
        $seatsSold = ReservationSeat::query()
            ->whereHas('reservation', fn ($query) => $query->confirmed())
            ->count();
        $reservationValue = (int) Reservation::query()->confirmed()->sum('total');

        $todaysScreenings = Screening::query()
            ->scheduled()
            ->whereDate('starts_at', today())
            ->with(['movie', 'auditorium'])
            ->withCount('seatHolds')
            ->orderBy('starts_at')
            ->get();

        $recentReservations = Reservation::query()
            ->with(['user', 'screening.movie'])
            ->latest()
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'publishedMovies',
            'upcomingScreenings',
            'confirmedReservations',
            'seatsSold',
            'reservationValue',
            'todaysScreenings',
            'recentReservations',
        ));
    }
}
