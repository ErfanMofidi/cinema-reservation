@extends('layouts.admin')

@section('title', 'Films')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Catalogue</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Films</h1>
            <p class="mt-2 text-sm text-muted">Publish new releases, maintain artwork, and archive films that have finished their run.</p>
        </div>
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary btn-sm w-fit">
            <x-icon name="plus" class="h-4 w-4" />
            New film
        </a>
    </div>

    <form method="GET" action="{{ route('admin.movies.index') }}" class="mt-6 flex flex-col gap-3 rounded-2xl border border-line bg-surface/60 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="field-label">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Film title or slug" class="field-input">
        </div>
        <div class="sm:w-44">
            <label for="status" class="field-label">Status</label>
            <select id="status" name="status" class="field-input">
                <option value="">All statuses</option>
                @foreach (\App\Enums\MovieStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if ($filters['q'] !== '' || $filters['status'] !== '')
                <a href="{{ route('admin.movies.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </div>
    </form>

    @if ($movies->isEmpty())
        <x-empty-state class="mt-6" title="No films found" description="Adjust the filters or add the first film to the catalogue." icon="film">
            <a href="{{ route('admin.movies.create') }}" class="btn btn-primary btn-sm">Add film</a>
        </x-empty-state>
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Film</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="hidden lg:table-cell">Genres</th>
                            <th scope="col" class="hidden md:table-cell">Released</th>
                            <th scope="col">Upcoming</th>
                            <th scope="col" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movies as $movie)
                            @php
                                $tone = match ($movie->status) {
                                    \App\Enums\MovieStatus::Published => 'success',
                                    \App\Enums\MovieStatus::Draft => 'muted',
                                    \App\Enums\MovieStatus::Archived => 'danger',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $movie->posterUrl() }}" alt="" class="h-14 w-10 shrink-0 rounded-md border border-line object-cover" aria-hidden="true">
                                        <div class="min-w-0">
                                            <p class="max-w-52 truncate font-medium text-ink">{{ $movie->title }}</p>
                                            <p class="max-w-52 truncate font-mono text-[10px] text-muted">{{ $movie->slug }}</p>
                                            <p class="mt-0.5 text-[10px] text-muted">{{ $movie->age_rating }} · {{ \App\Support\CinemaFormat::duration($movie->runtime_minutes) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td><x-status-badge :label="$movie->status->label()" :tone="$tone" /></td>
                                <td class="hidden max-w-56 lg:table-cell">
                                    <p class="text-xs text-muted">{{ $movie->genres->pluck('name')->join(', ') ?: '—' }}</p>
                                </td>
                                <td class="hidden whitespace-nowrap text-xs text-muted md:table-cell">
                                    {{ $movie->release_date?->format('M j, Y') ?? '—' }}
                                </td>
                                <td>
                                    <span class="text-xs {{ $movie->upcoming_screenings_count > 0 ? 'text-ink' : 'text-muted' }}">
                                        {{ $movie->upcoming_screenings_count }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        @if ($movie->status !== \App\Enums\MovieStatus::Archived && $movie->upcoming_screenings_count === 0)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.movies.archive', $movie) }}"
                                                data-confirm
                                                data-confirm-title="Archive {{ $movie->title }}?"
                                                data-confirm-message="The film will be hidden from the public programme. Historical screenings and reservations remain intact."
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger">Archive</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $movies->links() }}
    @endif
@endsection
