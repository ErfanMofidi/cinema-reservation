<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Screening;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 80),
            'genre' => (string) $request->query('genre', ''),
            'showing' => (string) $request->query('showing', ''),
            'sort' => (string) $request->query('sort', 'newest'),
        ];

        if (! in_array($filters['showing'], ['now', 'coming'], true)) {
            $filters['showing'] = '';
        }

        if (! in_array($filters['sort'], ['newest', 'title', 'runtime'], true)) {
            $filters['sort'] = 'newest';
        }

        $movies = Movie::query()
            ->published()
            ->with('genres')
            ->withCount([
                'screenings as upcoming_screenings_count' => fn (Builder $query) => $query->upcoming(),
            ])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $search = '%'.$filters['q'].'%';

                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'like', $search)
                        ->orWhere('tagline', 'like', $search)
                        ->orWhere('synopsis', 'like', $search);
                });
            })
            ->when($filters['genre'] !== '', function (Builder $query) use ($filters) {
                $query->whereHas('genres', fn (Builder $genres) => $genres->where('slug', $filters['genre']));
            })
            ->when($filters['showing'] === 'now', fn (Builder $query) => $query->nowShowing())
            ->when($filters['showing'] === 'coming', fn (Builder $query) => $query->comingSoon())
            ->when($filters['sort'] === 'title', fn (Builder $query) => $query->orderBy('title'))
            ->when($filters['sort'] === 'runtime', fn (Builder $query) => $query->orderBy('runtime_minutes'))
            ->when($filters['sort'] === 'newest', fn (Builder $query) => $query->orderByDesc('release_date'))
            ->paginate(12)
            ->withQueryString();

        $genres = Genre::query()
            ->whereHas('movies', fn (Builder $query) => $query->published())
            ->withCount([
                'movies as published_movies_count' => fn (Builder $query) => $query->published(),
            ])
            ->orderBy('name')
            ->get();

        return view('movies.index', compact('movies', 'genres', 'filters'));
    }

    public function show(Movie $movie): View
    {
        $movie = Movie::query()
            ->published()
            ->with([
                'genres',
                'screenings' => fn ($query) => $query
                    ->scheduled()
                    ->where('starts_at', '>', now())
                    ->with('auditorium')
                    ->withCount('seatHolds')
                    ->orderBy('starts_at'),
            ])
            ->findOrFail($movie->getKey());

        $screeningsByDate = $movie->screenings->groupBy(
            fn (Screening $screening) => $screening->starts_at->toDateString()
        );

        return view('movies.show', compact('movie', 'screeningsByDate'));
    }
}
