@php
    $selectedMovie = old('movie_id', $screening?->movie_id);
    $selectedAuditorium = old('auditorium_id', $screening?->auditorium_id);
    $startValue = old('starts_at', $screening?->starts_at?->format('Y-m-d\TH:i'));
    $endValue = old('ends_at', $screening?->ends_at?->format('Y-m-d\TH:i'));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" data-screening-form novalidate>
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <section class="card p-5 sm:p-6" aria-labelledby="screening-details-heading">
        <h2 id="screening-details-heading" class="font-display text-2xl text-ink">Screening details</h2>
        <p class="mt-1 text-xs text-muted">End time can be filled automatically from the film's runtime, then adjusted before saving.</p>

        @if ($movies->isEmpty())
            <x-alert type="warning" class="mt-4">
                <p class="font-medium">No published films available.</p>
                <p class="mt-1 text-xs text-warning/85">Publish a film in the catalogue before scheduling a screening.</p>
            </x-alert>
        @endif

        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <x-field name="movie_id" label="Film" :required="true">
                <select id="movie_id" name="movie_id" required class="field-input" data-movie-select @error('movie_id') aria-invalid="true" aria-describedby="movie_id-error" @enderror>
                    <option value="">Choose a film</option>
                    @foreach ($movies as $movie)
                        <option value="{{ $movie->id }}" data-runtime="{{ $movie->runtime_minutes }}" @selected((string) $selectedMovie === (string) $movie->id)>
                            {{ $movie->title }} · {{ \App\Support\CinemaFormat::duration($movie->runtime_minutes) }}
                        </option>
                    @endforeach
                </select>
            </x-field>

            <x-field name="auditorium_id" label="Auditorium" :required="true">
                <select id="auditorium_id" name="auditorium_id" required class="field-input" @error('auditorium_id') aria-invalid="true" aria-describedby="auditorium_id-error" @enderror>
                    <option value="">Choose a room</option>
                    @foreach ($auditoriums as $auditorium)
                        <option value="{{ $auditorium->id }}" @selected((string) $selectedAuditorium === (string) $auditorium->id)>
                            {{ $auditorium->code }} · {{ $auditorium->name }}
                        </option>
                    @endforeach
                </select>
            </x-field>

            <x-field name="starts_at" label="Starts at" :required="true" hint="Use the cinema's local time ({{ config('app.timezone') }}).">
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ $startValue }}" required class="field-input" data-screening-start @error('starts_at') aria-invalid="true" aria-describedby="starts_at-error" @enderror>
            </x-field>

            <x-field name="ends_at" label="Ends at" :required="true" hint="Must be after the start time.">
                <input id="ends_at" name="ends_at" type="datetime-local" value="{{ $endValue }}" required class="field-input" data-screening-end @error('ends_at') aria-invalid="true" aria-describedby="ends_at-error" @enderror>
            </x-field>

            <x-field name="base_price" label="Base price ({{ config('cinema.currency') }})" :required="true" hint="Standard seat price. Premium seats use a server-side multiplier.">
                <input id="base_price" name="base_price" type="number" min="0" max="100000000" step="1000" value="{{ old('base_price', $screening?->base_price ?? 350000) }}" required class="field-input" @error('base_price') aria-invalid="true" aria-describedby="base_price-error" @enderror>
            </x-field>
        </div>

        <p class="mt-5 flex items-start gap-2 text-xs leading-5 text-muted">
            <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gold" />
            Screenings in the same auditorium need {{ config('cinema.screening_buffer_minutes') }} minutes of clearance. The server checks every start and end time, not just the selected date.
        </p>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.screenings.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" @disabled($movies->isEmpty() || $auditoriums->isEmpty())>
            {{ $screening ? 'Save changes' : 'Schedule screening' }}
        </button>
    </div>
</form>
