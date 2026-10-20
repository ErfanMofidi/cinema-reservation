@extends('layouts.app')

@section('title', 'Films')
@section('meta_description', 'Browse the current and upcoming film programme.')

@section('content')
    <div class="container-cinema py-10 sm:py-14">
        <div class="max-w-3xl">
            <p class="eyebrow">The programme</p>
            <h1 class="mt-3 font-display text-5xl leading-none text-ink sm:text-6xl">Films at {{ config('cinema.name') }}</h1>
            <p class="mt-4 max-w-2xl text-sm leading-6 text-muted">
                Find what's playing, filter by genre, or look ahead to the next release. Every screening has reserved seating.
            </p>
        </div>

        <form method="GET" action="{{ route('movies.index') }}" class="mt-8 rounded-2xl border border-line bg-surface/70 p-4 sm:p-5" role="search">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr_1fr_auto] lg:items-end">
                <div>
                    <label for="q" class="field-label">Search</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-muted" aria-hidden="true">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input
                            id="q"
                            name="q"
                            type="search"
                            value="{{ $filters['q'] }}"
                            maxlength="80"
                            placeholder="Title, tagline, or story"
                            class="field-input pl-11"
                        >
                    </div>
                </div>

                <div>
                    <label for="genre" class="field-label">Genre</label>
                    <select id="genre" name="genre" class="field-input">
                        <option value="">All genres</option>
                        @foreach ($genres as $genre)
                            <option value="{{ $genre->slug }}" @selected($filters['genre'] === $genre->slug)>
                                {{ $genre->name }} ({{ $genre->published_movies_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="showing" class="field-label">Availability</label>
                    <select id="showing" name="showing" class="field-input">
                        <option value="">All films</option>
                        <option value="now" @selected($filters['showing'] === 'now')>Now showing</option>
                        <option value="coming" @selected($filters['showing'] === 'coming')>Coming soon</option>
                    </select>
                </div>

                <div>
                    <label for="sort" class="field-label">Sort by</label>
                    <select id="sort" name="sort" class="field-input">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest release</option>
                        <option value="title" @selected($filters['sort'] === 'title')>Title A–Z</option>
                        <option value="runtime" @selected($filters['sort'] === 'runtime')>Shortest runtime</option>
                    </select>
                </div>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    @if ($filters['q'] !== '' || $filters['genre'] !== '' || $filters['showing'] !== '' || $filters['sort'] !== 'newest')
                        <a href="{{ route('movies.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="mt-8 flex flex-col gap-2 border-b border-line-soft pb-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-muted">
                <span class="font-medium text-ink">{{ $movies->total() }}</span>
                {{ \Illuminate\Support\Str::plural('film', $movies->total()) }}
                @if ($filters['q'] !== '')
                    for “{{ $filters['q'] }}”
                @endif
            </p>
            @if ($filters['showing'] === 'now')
                <span class="badge badge-gold">Now showing</span>
            @elseif ($filters['showing'] === 'coming')
                <span class="badge badge-gold">Coming soon</span>
            @endif
        </div>

        @if ($movies->isEmpty())
            <x-empty-state
                class="mt-8"
                title="No films match those filters"
                description="Try a different title or genre, or clear the filters to see the full programme."
                icon="search"
            >
                <a href="{{ route('movies.index') }}" class="btn btn-secondary btn-sm">Clear filters</a>
            </x-empty-state>
        @else
            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-9 sm:gap-x-5 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($movies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>

            {{ $movies->links() }}
        @endif
    </div>
@endsection
