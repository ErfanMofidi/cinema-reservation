@extends('layouts.admin')

@section('title', 'Screenings')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Schedule</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Screenings</h1>
            <p class="mt-2 text-sm text-muted">Schedule showtimes with a 15-minute buffer between screenings in the same auditorium.</p>
        </div>
        <a href="{{ route('admin.screenings.create') }}" class="btn btn-primary btn-sm w-fit">
            <x-icon name="plus" class="h-4 w-4" />
            New screening
        </a>
    </div>

    <form method="GET" action="{{ route('admin.screenings.index') }}" class="mt-6 rounded-2xl border border-line bg-surface/60 p-4">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div>
                <label for="q" class="field-label">Film search</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Title" class="field-input">
            </div>
            <div>
                <label for="date" class="field-label">Date</label>
                <input id="date" name="date" type="date" value="{{ $filters['date'] }}" class="field-input">
            </div>
            <div>
                <label for="movie" class="field-label">Film</label>
                <select id="movie" name="movie" class="field-input">
                    <option value="">All films</option>
                    @foreach ($movies as $movie)
                        <option value="{{ $movie->id }}" @selected($filters['movie'] === (string) $movie->id)>{{ $movie->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="auditorium" class="field-label">Auditorium</label>
                <select id="auditorium" name="auditorium" class="field-input">
                    <option value="">All rooms</option>
                    @foreach ($auditoriums as $auditorium)
                        <option value="{{ $auditorium->id }}" @selected($filters['auditorium'] === (string) $auditorium->id)>{{ $auditorium->code }} · {{ $auditorium->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="field-label">Status</label>
                <select id="status" name="status" class="field-input">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if (array_filter($filters))
                    <a href="{{ route('admin.screenings.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </div>
        </div>
    </form>

    @if ($screenings->isEmpty())
        <x-empty-state class="mt-6" title="No screenings found" description="Adjust the filters or schedule a new showtime." icon="calendar">
            <a href="{{ route('admin.screenings.create') }}" class="btn btn-primary btn-sm">Add screening</a>
        </x-empty-state>
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Film</th>
                            <th scope="col" class="hidden md:table-cell">Auditorium</th>
                            <th scope="col" class="hidden lg:table-cell">Price</th>
                            <th scope="col">Booked</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($screenings as $screening)
                            @php
                                $tone = match ($screening->status) {
                                    \App\Enums\ScreeningStatus::Scheduled => 'gold',
                                    \App\Enums\ScreeningStatus::Cancelled => 'danger',
                                    \App\Enums\ScreeningStatus::Completed => 'muted',
                                };
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">
                                    <p class="font-mono text-xs text-ink">{{ $screening->starts_at->format('M j, Y') }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">{{ $screening->starts_at->format('H:i') }}–{{ $screening->ends_at->format('H:i') }}</p>
                                </td>
                                <td>
                                    <p class="max-w-48 truncate font-medium">{{ $screening->movie->title }}</p>
                                    <p class="text-[10px] text-muted">{{ $screening->movie->age_rating }} · {{ \App\Support\CinemaFormat::duration($screening->movie->runtime_minutes) }}</p>
                                </td>
                                <td class="hidden text-xs text-muted md:table-cell">{{ $screening->auditorium->code }} · {{ $screening->auditorium->name }}</td>
                                <td class="hidden whitespace-nowrap text-xs text-muted lg:table-cell">{{ \App\Support\CinemaFormat::money($screening->base_price) }}</td>
                                <td><span class="text-xs {{ $screening->reservations_count > 0 ? 'text-ink' : 'text-muted' }}">{{ $screening->reservations_count }}</span></td>
                                <td><x-status-badge :label="$screening->status->label()" :tone="$tone" /></td>
                                <td>
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('admin.screenings.edit', $screening) }}" class="btn btn-secondary btn-sm">Edit</a>

                                        @if ($screening->status === \App\Enums\ScreeningStatus::Scheduled)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.screenings.cancel', $screening) }}"
                                                data-confirm
                                                data-confirm-title="Cancel this screening?"
                                                data-confirm-message="The screening will be marked cancelled. Any confirmed reservations will also be cancelled and their seats released."
                                            >
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger">Cancel</button>
                                            </form>
                                        @endif

                                        @if ($screening->reservations_count === 0)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.screenings.destroy', $screening) }}"
                                                data-confirm
                                                data-confirm-title="Delete this screening?"
                                                data-confirm-message="This screening has no reservations and will be permanently removed."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger">Delete</button>
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

        {{ $screenings->links() }}
    @endif
@endsection
