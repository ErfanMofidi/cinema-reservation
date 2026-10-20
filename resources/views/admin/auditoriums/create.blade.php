@extends('layouts.admin')

@section('title', 'New auditorium')

@section('content')
    <div class="max-w-3xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.auditoriums.index') }}" class="hover:text-gold">Auditoriums</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">New auditorium</li>
            </ol>
        </nav>

        <div class="mt-5">
            <p class="eyebrow">Venue</p>
            <h1 class="mt-2 font-display text-4xl text-ink">Add an auditorium</h1>
            <p class="mt-2 text-sm text-muted">A rectangular seat layout is generated on save. Rows begin at A; the front row includes accessible seats at both ends.</p>
        </div>

        <form method="POST" action="{{ route('admin.auditoriums.store') }}" class="card mt-7 space-y-6 p-5 sm:p-6" novalidate>
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="name" label="Name" :required="true">
                    <input id="name" name="name" type="text" maxlength="120" value="{{ old('name') }}" required placeholder="The Grand Hall" class="field-input" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                </x-field>

                <x-field name="code" label="Code" :required="true" hint="Capital letters, numbers, and hyphens.">
                    <input id="code" name="code" type="text" maxlength="24" value="{{ old('code') }}" required placeholder="GRAND" class="field-input uppercase" @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
                </x-field>

                <x-field name="screen_label" label="Screen label">
                    <input id="screen_label" name="screen_label" type="text" maxlength="60" value="{{ old('screen_label') }}" placeholder="Screen 1" class="field-input" @error('screen_label') aria-invalid="true" aria-describedby="screen_label-error" @enderror>
                </x-field>

                <div class="flex items-end">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2.5 text-sm text-ink/85">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-line bg-night text-gold focus:ring-2 focus:ring-gold/30">
                        Active and available for screenings
                    </label>
                </div>
            </div>

            <fieldset class="border-t border-line-soft pt-5">
                <legend class="field-label">Seat layout</legend>
                <p class="mb-4 text-xs text-muted">The last rows are premium; accessible seats are placed at the ends of row A.</p>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-field name="rows" label="Rows" :required="true">
                        <input id="rows" name="rows" type="number" min="1" max="26" value="{{ old('rows', 8) }}" required class="field-input" @error('rows') aria-invalid="true" aria-describedby="rows-error" @enderror>
                    </x-field>
                    <x-field name="seats_per_row" label="Seats per row" :required="true">
                        <input id="seats_per_row" name="seats_per_row" type="number" min="1" max="30" value="{{ old('seats_per_row', 12) }}" required class="field-input" @error('seats_per_row') aria-invalid="true" aria-describedby="seats_per_row-error" @enderror>
                    </x-field>
                    <x-field name="premium_rows" label="Premium rows" :required="true">
                        <input id="premium_rows" name="premium_rows" type="number" min="0" max="26" value="{{ old('premium_rows', 2) }}" required class="field-input" @error('premium_rows') aria-invalid="true" aria-describedby="premium_rows-error" @enderror>
                    </x-field>
                </div>
            </fieldset>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create auditorium</button>
            </div>
        </form>
    </div>
@endsection
