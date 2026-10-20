@php
    $selectedGenres = collect(old('genres', $movie?->genres->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $selectedPoster = old('poster_path', $movie?->poster_path);
    $selectedBackdrop = old('backdrop_path', $movie?->backdrop_path);
    $selectedStatus = old('status', $movie?->status?->value ?? \App\Enums\MovieStatus::Draft->value);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" novalidate>
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <section class="card p-5 sm:p-6" aria-labelledby="film-details-heading">
        <h2 id="film-details-heading" class="font-display text-2xl text-ink">Film details</h2>
        <p class="mt-1 text-xs text-muted">This information appears on the public film page.</p>

        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <x-field name="title" label="Title" :required="true">
                <input id="title" name="title" type="text" value="{{ old('title', $movie?->title) }}" required class="field-input" @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>
            </x-field>

            <x-field name="slug" label="URL slug" :required="true" hint="Lowercase words separated by hyphens. Leave blank on create to generate one from the title.">
                <input id="slug" name="slug" type="text" value="{{ old('slug', $movie?->slug) }}" class="field-input" @error('slug') aria-invalid="true" aria-describedby="slug-error" @enderror>
            </x-field>

            <div class="lg:col-span-2">
                <x-field name="tagline" label="Tagline" hint="Optional one-line hook shown beneath the title.">
                    <input id="tagline" name="tagline" type="text" value="{{ old('tagline', $movie?->tagline) }}" maxlength="240" class="field-input" @error('tagline') aria-invalid="true" aria-describedby="tagline-error" @enderror>
                </x-field>
            </div>

            <div class="lg:col-span-2">
                <x-field name="synopsis" label="Synopsis" :required="true" hint="At least 40 characters. Plain text; line breaks become paragraphs.">
                    <textarea id="synopsis" name="synopsis" rows="7" required class="field-input" @error('synopsis') aria-invalid="true" aria-describedby="synopsis-error" @enderror>{{ old('synopsis', $movie?->synopsis) }}</textarea>
                </x-field>
            </div>
        </div>
    </section>

    <section class="card p-5 sm:p-6" aria-labelledby="film-artwork-heading">
        <h2 id="film-artwork-heading" class="font-display text-2xl text-ink">Artwork</h2>
        <p class="mt-1 text-xs text-muted">Choose from the local media library. A fallback poster is used when none is selected.</p>

        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <div class="flex gap-4">
                <img src="{{ \App\Support\Media::poster(old('poster_path', $movie?->poster_path)) }}" alt="" class="h-32 w-[5.5rem] shrink-0 rounded-lg border border-line object-cover" aria-hidden="true">
                <div class="min-w-0 flex-1">
                    <x-field name="poster_path" label="Poster">
                        <select id="poster_path" name="poster_path" class="field-input" @error('poster_path') aria-invalid="true" aria-describedby="poster_path-error" @enderror>
                            <option value="">Use fallback poster</option>
                            @foreach ($posterOptions as $option)
                                <option value="{{ $option }}" @selected($selectedPoster === $option)>{{ basename($option) }}</option>
                            @endforeach
                        </select>
                    </x-field>
                </div>
            </div>

            <div class="flex gap-4">
                @if ($selectedBackdrop)
                    <img src="{{ \App\Support\Media::image($selectedBackdrop, \App\Support\Media::poster(null)) }}" alt="" class="h-32 w-48 shrink-0 rounded-lg border border-line object-cover" aria-hidden="true">
                @else
                    <span class="flex h-32 w-48 shrink-0 items-center justify-center rounded-lg border border-dashed border-line text-xs text-muted">No backdrop</span>
                @endif
                <div class="min-w-0 flex-1">
                    <x-field name="backdrop_path" label="Backdrop">
                        <select id="backdrop_path" name="backdrop_path" class="field-input" @error('backdrop_path') aria-invalid="true" aria-describedby="backdrop_path-error" @enderror>
                            <option value="">No backdrop</option>
                            @foreach ($backdropOptions as $option)
                                <option value="{{ $option }}" @selected($selectedBackdrop === $option)>{{ basename($option) }}</option>
                            @endforeach
                        </select>
                    </x-field>
                </div>
            </div>
        </div>
    </section>

    <section class="card p-5 sm:p-6" aria-labelledby="film-meta-heading">
        <h2 id="film-meta-heading" class="font-display text-2xl text-ink">Classification &amp; release</h2>

        <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-field name="runtime_minutes" label="Runtime (minutes)" :required="true">
                <input id="runtime_minutes" name="runtime_minutes" type="number" min="1" max="400" value="{{ old('runtime_minutes', $movie?->runtime_minutes) }}" required class="field-input" @error('runtime_minutes') aria-invalid="true" aria-describedby="runtime_minutes-error" @enderror>
            </x-field>

            <x-field name="age_rating" label="Age rating" :required="true">
                <input id="age_rating" name="age_rating" type="text" maxlength="12" value="{{ old('age_rating', $movie?->age_rating) }}" required placeholder="PG-13" class="field-input" @error('age_rating') aria-invalid="true" aria-describedby="age_rating-error" @enderror>
            </x-field>

            <x-field name="language" label="Language" :required="true">
                <input id="language" name="language" type="text" maxlength="40" value="{{ old('language', $movie?->language ?? 'English') }}" required class="field-input" @error('language') aria-invalid="true" aria-describedby="language-error" @enderror>
            </x-field>

            <x-field name="release_date" label="Release date">
                <input id="release_date" name="release_date" type="date" value="{{ old('release_date', $movie?->release_date?->toDateString()) }}" class="field-input" @error('release_date') aria-invalid="true" aria-describedby="release_date-error" @enderror>
            </x-field>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <x-field name="status" label="Publication status" :required="true" hint="Archived films disappear from the public programme but keep their history.">
                <select id="status" name="status" required class="field-input" @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </x-field>

            <fieldset>
                <legend class="field-label">Genres <span class="text-gold" aria-hidden="true">*</span></legend>
                <div class="grid gap-2 rounded-xl border border-line bg-night/40 p-4 sm:grid-cols-2">
                    @foreach ($genres as $genre)
                        <label class="flex items-center gap-2 text-sm text-ink/85">
                            <input type="checkbox" name="genres[]" value="{{ $genre->id }}" @checked(in_array($genre->id, $selectedGenres, true)) class="h-4 w-4 rounded border-line bg-night text-gold focus:ring-2 focus:ring-gold/30">
                            {{ $genre->name }}
                        </label>
                    @endforeach
                </div>
                @error('genres')
                    <p class="field-error" id="genres-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $movie ? 'Save changes' : 'Create film' }}</button>
    </div>
</form>
