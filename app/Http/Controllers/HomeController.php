<?php

namespace App\Http\Controllers;

use App\Models\Auditorium;
use App\Models\Movie;
use App\Models\Screening;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $auditoriums = Auditorium::query()
            ->active()
            ->orderBy('name')
            ->get(['name', 'screen_label']);

        $nowShowing = Movie::query()
            ->nowShowing()
            ->with('genres')
            ->orderByDesc('release_date')
            ->limit(4)
            ->get();
        $comingSoon = Movie::query()
            ->comingSoon()
            ->with('genres')
            ->orderBy('release_date')
            ->limit(3)
            ->get();

        $featured = Movie::query()
            ->nowShowing()
            ->with('genres')
            ->orderByDesc('release_date')
            ->first();

        $tonight = Screening::query()
            ->bookable()
            ->whereDate('starts_at', today())
            ->with(['movie:id,title,slug', 'auditorium:id,name,code'])
            ->withCount('seatHolds')
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return view('home', compact('nowShowing', 'comingSoon', 'featured', 'tonight', 'auditoriums'));
    }
}
