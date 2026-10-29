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
        <h2 id="screening-details-heading" class="font-display text-2xl text-ink">{{ __('admin.screenings.screening_details') }}</h2>
        <p class="mt-1 text-xs text-muted">{{ __('admin.screenings.end_time_auto') }}</p>

        @if ($movies->isEmpty())
            <x-alert type="warning" class="mt-4">
                <p class="font-medium">{{ __('admin.screenings.no_published_films') }}</p>
                <p class="mt-1 text-xs text-warning/85">{{ __('admin.screenings.publish_film_first') }}</p>
            </x-alert>
        @endif

        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <x-field name="movie_id" label="{{ __('admin.screenings.film_label') }}" :required="true">
                <select id="movie_id" name="movie_id" required class="field-input" data-movie-select @error('movie_id') aria-invalid="true" aria-describedby="movie_id-error" @enderror>
                    <option value="">{{ __('admin.screenings.choose_film') }}</option>
                    @foreach ($movies as $movie)
                        <option value="{{ $movie->id }}" data-runtime="{{ $movie->runtime_minutes }}" @selected((string) $selectedMovie === (string) $movie->id)>
                            {{ $movie->title }} · {{ \App\Support\CinemaFormat::duration($movie->runtime_minutes) }}
                        </option>
                    @endforeach
                </select>
            </x-field>

            <x-field name="auditorium_id" label="{{ __('admin.screenings.auditorium_label') }}" :required="true">
                <select id="auditorium_id" name="auditorium_id" required class="field-input" @error('auditorium_id') aria-invalid="true" aria-describedby="auditorium_id-error" @enderror>
                    <option value="">{{ __('admin.auditoriums.choose_room') }}</option>
                    @foreach ($auditoriums as $auditorium)
                        <option value="{{ $auditorium->id }}" @selected((string) $selectedAuditorium === (string) $auditorium->id)>
                            {{ $auditorium->code }} · {{ $auditorium->name }}
                        </option>
                    @endforeach
                </select>
            </x-field>

            <x-field name="starts_at" label="{{ __('admin.screenings.starts_at') }}" :required="true" hint="{{ __('admin.screenings.local_time', ['timezone' => config('app.timezone')]) }}">
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ $startValue }}" required class="field-input" data-screening-start @error('starts_at') aria-invalid="true" aria-describedby="starts_at-error" @enderror>
            </x-field>

            <x-field name="ends_at" label="{{ __('admin.screenings.ends_at') }}" :required="true" hint="{{ __('admin.screenings.must_be_after_start') }}">
                <input id="ends_at" name="ends_at" type="datetime-local" value="{{ $endValue }}" required class="field-input" data-screening-end @error('ends_at') aria-invalid="true" aria-describedby="ends_at-error" @enderror>
            </x-field>

            <x-field name="base_price" label="{{ __('admin.screenings.base_price', ['currency' => config('cinema.currency')]) }}" :required="true" hint="{{ __('admin.screenings.standard_seat_price') }}">
                <input id="base_price" name="base_price" type="number" min="0" max="100000000" step="1000" value="{{ old('base_price', $screening?->base_price ?? 350000) }}" required class="field-input" @error('base_price') aria-invalid="true" aria-describedby="base_price-error" @enderror>
            </x-field>
        </div>

        <p class="mt-5 flex items-start gap-2 text-xs leading-5 text-muted">
            <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gold" />
            {{ __('admin.screenings.screening_buffer', ['minutes' => config('cinema.screening_buffer_minutes')]) }}
        </p>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.screenings.index') }}" class="btn btn-secondary">{{ __('admin.screenings.cancel') }}</a>
        <button type="submit" class="btn btn-primary" @disabled($movies->isEmpty() || $auditoriums->isEmpty())>
            {{ $screening ? __('admin.screenings.save_changes') : __('admin.screenings.schedule') }}
        </button>
    </div>
</form>
