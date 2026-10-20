@extends('layouts.admin')

@section('title', 'Auditoriums')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Venue</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Auditoriums</h1>
            <p class="mt-2 text-sm text-muted">Manage rooms and seat layouts. Layouts can only be rebuilt before a room has screenings.</p>
        </div>
        <a href="{{ route('admin.auditoriums.create') }}" class="btn btn-primary btn-sm w-fit">
            <x-icon name="plus" class="h-4 w-4" />
            New auditorium
        </a>
    </div>

    @if ($auditoriums->isEmpty())
        <x-empty-state class="mt-6" title="No auditoriums yet" description="Create a room and generate its seat layout before scheduling screenings." icon="building">
            <a href="{{ route('admin.auditoriums.create') }}" class="btn btn-primary btn-sm">Add auditorium</a>
        </x-empty-state>
    @else
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($auditoriums as $auditorium)
                <article class="card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-mono text-[11px] tracking-wider text-gold">{{ $auditorium->code }}</p>
                            <h2 class="mt-1 font-display text-2xl text-ink">{{ $auditorium->name }}</h2>
                            <p class="mt-0.5 text-xs text-muted">{{ $auditorium->screen_label ?: 'No screen label' }}</p>
                        </div>
                        <x-status-badge :label="$auditorium->is_active ? 'Active' : 'Inactive'" :tone="$auditorium->is_active ? 'success' : 'muted'" />
                    </div>

                    <dl class="mt-5 grid grid-cols-3 gap-2 border-t border-line-soft pt-4 text-center">
                        <div>
                            <dt class="text-[9px] uppercase tracking-[0.14em] text-muted">Capacity</dt>
                            <dd class="mt-1 font-display text-2xl text-ink">{{ $auditorium->capacity }}</dd>
                        </div>
                        <div>
                            <dt class="text-[9px] uppercase tracking-[0.14em] text-muted">Seat rows</dt>
                            <dd class="mt-1 font-display text-2xl text-ink">{{ $auditorium->seats_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-[9px] uppercase tracking-[0.14em] text-muted">Screenings</dt>
                            <dd class="mt-1 font-display text-2xl text-ink">{{ $auditorium->screenings_count }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex justify-end">
                        <a href="{{ route('admin.auditoriums.edit', $auditorium) }}" class="btn btn-secondary btn-sm">Manage</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $auditoriums->links() }}
    @endif
@endsection
