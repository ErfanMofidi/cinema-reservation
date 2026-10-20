<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MovieStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MovieRequest;
use App\Models\Genre;
use App\Models\Movie;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MovieController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 80),
            'status' => (string) $request->query('status', ''),
        ];

        if (! in_array($filters['status'], array_column(MovieStatus::cases(), 'value'), true)) {
            $filters['status'] = '';
        }

        $movies = Movie::query()
            ->with('genres')
            ->withCount([
                'screenings as upcoming_screenings_count' => fn (Builder $query) => $query->upcoming(),
            ])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $search = '%'.$filters['q'].'%';
                $query->where(fn (Builder $query) => $query
                    ->where('title', 'like', $search)
                    ->orWhere('slug', 'like', $search));
            })
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.movies.index', compact('movies', 'filters'));
    }

    public function create(): View
    {
        return view('admin.movies.create', $this->formData());
    }

    public function store(MovieRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $genreIds = $data['genres'];
        unset($data['genres']);

        $movie = Movie::create($data);
        $movie->genres()->sync($genreIds);

        return redirect()
            ->route('admin.movies.index')
            ->with('success', "{$movie->title} was added to the catalogue.");
    }

    public function edit(Movie $movie): View
    {
        $movie->load('genres');

        return view('admin.movies.edit', $this->formData($movie));
    }

    public function update(MovieRequest $request, Movie $movie): RedirectResponse
    {
        $data = $request->validated();
        $genreIds = $data['genres'];
        unset($data['genres']);

        if (MovieStatus::from($data['status']) === MovieStatus::Archived
            && $movie->screenings()->upcoming()->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Cancel the upcoming screenings before archiving this film.',
            ]);
        }

        $movie->update($data);
        $movie->genres()->sync($genreIds);

        return redirect()
            ->route('admin.movies.edit', $movie)
            ->with('success', 'The film details were updated.');
    }

    public function archive(Movie $movie): RedirectResponse
    {
        if ($movie->status === MovieStatus::Archived) {
            return back()->with('status', 'This film is already archived.');
        }

        if ($movie->screenings()->upcoming()->exists()) {
            return back()->with('error', 'Cancel the upcoming screenings before archiving this film.');
        }

        $movie->update(['status' => MovieStatus::Archived]);

        return redirect()
            ->route('admin.movies.index')
            ->with('success', "{$movie->title} was archived and is hidden from the public programme.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Movie $movie = null): array
    {
        return [
            'movie' => $movie,
            'genres' => Genre::query()->orderBy('name')->get(['id', 'name']),
            'posterOptions' => Media::posterOptions(),
            'backdropOptions' => Media::backdropOptions(),
            'statuses' => MovieStatus::cases(),
        ];
    }
}
