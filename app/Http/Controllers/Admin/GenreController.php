<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenreRequest;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 80);

        $genres = Genre::query()
            ->withCount('movies')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.genres.index', compact('genres', 'search'));
    }

    public function create(): View
    {
        return view('admin.genres.create');
    }

    public function store(GenreRequest $request): RedirectResponse
    {
        $genre = Genre::create($request->validated());

        return redirect()
            ->route('admin.genres.index')
            ->with('success', "{$genre->name} was added.");
    }

    public function edit(Genre $genre): View
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(GenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()
            ->route('admin.genres.index')
            ->with('success', "{$genre->name} was updated.");
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        $movieCount = $genre->movies()->count();

        if ($movieCount > 0) {
            return back()->with(
                'error',
                "{$genre->name} is assigned to {$movieCount} film(s). Remove it from those films before deleting it."
            );
        }

        $genre->delete();

        return redirect()
            ->route('admin.genres.index')
            ->with('success', "{$genre->name} was deleted.");
    }
}
