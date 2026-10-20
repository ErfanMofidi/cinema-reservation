@extends('layouts.admin')

@section('title', 'Edit '.$auditorium->name)

@section('content')
    <div class="max-w-3xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.auditoriums.index') }}" class="hover:text-gold">Auditoriums</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="max-w-[60vw] truncate text-ink/70">{{ $auditorium->name }}</li>
            </ol>
        </nav>

        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Venue</p>
                <h1 class="mt-2 font-display text-4xl text-ink">{{ $auditorium->name }}</h1>
                <p class="mt-2 text-sm text-muted">{{ $auditorium->capacity }} seats · {{ $auditorium->screenings_count }} screenings · {{ $auditorium->seats_count }} seat records</p>
            </div>
            <x-status-badge :label="$auditorium->is_active ? 'Active' : 'Inactive'" :tone="$auditorium->is_active ? 'success' : 'muted'" />
        </div>

        <form method="POST" action="{{ route('admin.auditoriums.update', $auditorium) }}" class="card mt-7 space-y-5 p-5 sm:p-6" novalidate>
            @csrf
            @method('PATCH')

            <h2 class="font-display text-2xl text-ink">Room details</h2>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="name" label="Name" :required="true">
                    <input id="name" name="name" type="text" maxlength="120" value="{{ old('name', $auditorium->name) }}" required class="field-input" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                </x-field>

                <x-field name="code" label="Code" :required="true">
                    <input id="code" name="code" type="text" maxlength="24" value="{{ old('code', $auditorium->code) }}" required class="field-input uppercase" @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
                </x-field>

                <x-field name="screen_label" label="Screen label">
                    <input id="screen_label" name="screen_label" type="text" maxlength="60" value="{{ old('screen_label', $auditorium->screen_label) }}" class="field-input" @error('screen_label') aria-invalid="true" aria-describedby="screen_label-error" @enderror>
                </x-field>

                <div class="flex items-end">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2.5 text-sm text-ink/85">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $auditorium->is_active)) class="h-4 w-4 rounded border-line bg-night text-gold focus:ring-2 focus:ring-gold/30">
                        Active and available for new screenings
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Save details</button>
            </div>
        </form>

        <section class="card mt-6 p-5 sm:p-6" aria-labelledby="layout-heading">
            <h2 id="layout-heading" class="font-display text-2xl text-ink">Seat layout</h2>

            @if ($auditorium->screenings_count > 0)
                <p class="mt-2 text-sm leading-6 text-muted">
                    The layout is locked because this auditorium has screenings. Deleting and rebuilding seats would break reservation history. Create a new auditorium for a different layout.
                </p>
                <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl border border-line bg-night/40 p-4 text-center">
                    <div>
                        <p class="text-[9px] uppercase tracking-[0.14em] text-muted">Rows</p>
                        <p class="mt-1 font-display text-2xl text-ink">{{ $layoutDefaults['rows'] }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-[0.14em] text-muted">Seats / row</p>
                        <p class="mt-1 font-display text-2xl text-ink">{{ $layoutDefaults['seats_per_row'] }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-[0.14em] text-muted">Premium rows</p>
                        <p class="mt-1 font-display text-2xl text-ink">{{ $layoutDefaults['premium_rows'] }}</p>
                    </div>
                </div>
            @else
                <p class="mt-2 text-sm leading-6 text-muted">
                    Rebuilding replaces every seat in this auditorium. This is safe only because no screenings exist yet.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.auditoriums.layout', $auditorium) }}"
                    class="mt-4"
                    data-confirm
                    data-confirm-title="Rebuild the seat layout?"
                    data-confirm-message="All existing seats in this auditorium will be replaced. This cannot be undone."
                >
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-3">
                        <x-field name="rows" label="Rows" :required="true">
                            <input id="rows" name="rows" type="number" min="1" max="26" value="{{ old('rows', $layoutDefaults['rows']) }}" required class="field-input" @error('rows') aria-invalid="true" aria-describedby="rows-error" @enderror>
                        </x-field>
                        <x-field name="seats_per_row" label="Seats per row" :required="true">
                            <input id="seats_per_row" name="seats_per_row" type="number" min="1" max="30" value="{{ old('seats_per_row', $layoutDefaults['seats_per_row']) }}" required class="field-input" @error('seats_per_row') aria-invalid="true" aria-describedby="seats_per_row-error" @enderror>
                        </x-field>
                        <x-field name="premium_rows" label="Premium rows" :required="true">
                            <input id="premium_rows" name="premium_rows" type="number" min="0" max="26" value="{{ old('premium_rows', $layoutDefaults['premium_rows']) }}" required class="field-input" @error('premium_rows') aria-invalid="true" aria-describedby="premium_rows-error" @enderror>
                        </x-field>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn btn-danger btn-sm">Rebuild layout</button>
                    </div>
                </form>
            @endif
        </section>

        <div class="mt-6">
            <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-secondary btn-sm">
                <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                All auditoriums
            </a>
        </div>
    </div>
@endsection
