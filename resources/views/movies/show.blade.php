@extends('layouts.app')

@section('title', $movie->title)
@section('meta_description', \Illuminate\Support\Str::limit($movie->synopsis, 150))

@section('content')
    @php
        $heroImage = $movie->backdropUrl() ?? $movie->posterUrl();
        $firstDate = $screeningsByDate->keys()->first();
    @endphp

    <section class="relative isolate overflow-hidden border-b border-line-soft">
        <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-35" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-t from-void via-void/80 to-void/40"></div>

        <div class="container-cinema relative py-10 sm:py-14">
            <nav aria-label="Breadcrumb" class="text-xs text-muted">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('movies.index') }}" class="transition hover:text-gold">Films</a></li>
                    <li aria-hidden="true" class="text-line">/</li>
                    <li aria-current="page" class="max-w-[60vw] truncate text-ink/70">{{ $movie->title }}</li>
                </ol>
            </nav>

            <div class="mt-8 grid gap-8 sm:grid-cols-[10rem_1fr] lg:grid-cols-[14rem_1fr] lg:items-end">
                <img
                    src="{{ $movie->posterUrl() }}"
                    alt="Poster for {{ $movie->title }}"
                    class="aspect-[2/3] w-40 rounded-2xl border border-line object-cover shadow-2xl shadow-black/70 sm:w-full"
                >

                <div class="max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge badge-muted">{{ $movie->age_rating }}</span>
                        @if ($movie->release_date?->isFuture())
                            <span class="badge badge-gold">Coming soon</span>
                        @else
                            <span class="badge badge-gold">Now showing</span>
                        @endif
                    </div>

                    <h1 class="mt-4 font-display text-5xl leading-[0.95] text-ink sm:text-6xl lg:text-7xl">{{ $movie->title }}</h1>

                    @if ($movie->tagline)
                        <p class="mt-3 font-display text-xl italic text-gold-soft/90 sm:text-2xl">{{ $movie->tagline }}</p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-ink/65">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="clock" class="h-3.5 w-3.5" />
                            {{ \App\Support\CinemaFormat::duration($movie->runtime_minutes) }}
                        </span>
                        <span class="text-line" aria-hidden="true">/</span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="language" class="h-3.5 w-3.5" />
                            {{ $movie->language }}
                        </span>
                        @if ($movie->release_date)
                            <span class="text-line" aria-hidden="true">/</span>
                            <span>Released {{ $movie->release_date->format('M j, Y') }}</span>
                        @endif
                        @if ($movie->genres->isNotEmpty())
                            <span class="text-line" aria-hidden="true">/</span>
                            <span>{{ $movie->genres->pluck('name')->join(' · ') }}</span>
                        @endif
                    </div>

                    <div class="mt-7 flex flex-wrap gap-3">
                        @if ($firstDate)
                            <a href="#showtimes" class="btn btn-primary">
                                <x-icon name="ticket" class="h-4 w-4" />
                                Choose a showtime
                            </a>
                        @endif
                        <a href="{{ route('movies.index') }}" class="btn btn-secondary">Back to films</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container-cinema py-10 sm:py-14">
        <div class="grid gap-8 lg:grid-cols-[1fr_20rem] lg:items-start">
            <div>
                <section aria-labelledby="synopsis-heading">
                    <p class="eyebrow">The story</p>
                    <h2 id="synopsis-heading" class="mt-2 font-display text-3xl text-ink">Synopsis</h2>
                    <div class="prose-cinema mt-4">
                        @foreach (preg_split('/\n+/', trim($movie->synopsis)) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </section>

                <section id="showtimes" class="mt-12 scroll-mt-24" aria-labelledby="showtimes-heading">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="eyebrow">Reserve a seat</p>
                            <h2 id="showtimes-heading" class="mt-2 font-display text-3xl text-ink">Showtimes</h2>
                        </div>
                        @if ($screeningsByDate->isNotEmpty())
                            <p class="text-xs text-muted">All times are local · Booking closes 15 minutes before showtime</p>
                        @endif
                    </div>

                    @if ($screeningsByDate->isEmpty())
                        <x-empty-state
                            class="mt-6"
                            title="Tickets are not on sale yet"
                            description="{{ $movie->release_date?->isFuture() ? 'This film opens on '.$movie->release_date->format('F j, Y').'. Check back closer to the release date.' : 'There are no upcoming screenings for this film right now. Browse the programme for something else.' }}"
                            icon="calendar"
                        >
                            <a href="{{ route('movies.index', ['showing' => 'now']) }}" class="btn btn-secondary btn-sm">See what's showing</a>
                        </x-empty-state>
                    @else
                        <nav class="no-scrollbar mt-5 flex gap-2 overflow-x-auto pb-1" aria-label="Jump to a showtime date">
                            @foreach ($screeningsByDate as $date => $dayScreenings)
                                @php $day = \Carbon\Carbon::parse($date); @endphp
                                <a
                                    href="#date-{{ $date }}"
                                    class="flex shrink-0 flex-col rounded-xl border border-line bg-surface px-4 py-2.5 text-center transition hover:border-gold/50"
                                >
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">
                                        {{ $day->isToday() ? 'Today' : ($day->isTomorrow() ? 'Tomorrow' : $day->format('D')) }}
                                    </span>
                                    <span class="mt-0.5 text-sm font-medium text-ink">{{ $day->format('M j') }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-8 space-y-8">
                            @foreach ($screeningsByDate as $date => $dayScreenings)
                                @php $day = \Carbon\Carbon::parse($date); @endphp
                                <section id="date-{{ $date }}" class="scroll-mt-24" aria-labelledby="date-heading-{{ $date }}">
                                    <div class="flex items-baseline justify-between gap-4">
                                        <h3 id="date-heading-{{ $date }}" class="font-display text-2xl text-ink">
                                            {{ $day->isToday() ? 'Today' : ($day->isTomorrow() ? 'Tomorrow' : $day->format('l')) }}
                                            <span class="ml-2 font-sans text-xs font-normal text-muted">{{ $day->format('F j') }}</span>
                                        </h3>
                                        <span class="text-[10px] uppercase tracking-[0.16em] text-muted">{{ $dayScreenings->count() }} {{ \Illuminate\Support\Str::plural('screening', $dayScreenings->count()) }}</span>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($dayScreenings as $screening)
                                            @php
                                                $remaining = max(0, $screening->auditorium->capacity - $screening->seat_holds_count);
                                                $bookingOpen = $screening->isBookingOpen();
                                            @endphp
                                            <a
                                                href="{{ route('screenings.show', $screening) }}"
                                                class="group grid gap-3 rounded-2xl border border-line bg-surface/70 p-4 transition hover:border-gold/45 sm:grid-cols-[7.5rem_1fr_auto_auto] sm:items-center sm:gap-5"
                                            >
                                                <div class="flex items-baseline gap-2 sm:block">
                                                    <span class="font-mono text-lg font-semibold text-ink group-hover:text-gold">{{ \App\Support\CinemaFormat::time($screening->starts_at) }}</span>
                                                    <span class="text-[10px] text-muted sm:mt-0.5 sm:block">until {{ \App\Support\CinemaFormat::time($screening->ends_at) }}</span>
                                                </div>

                                                <div>
                                                    <p class="text-sm font-medium text-ink">{{ $screening->auditorium->name }}</p>
                                                    <p class="mt-0.5 text-[11px] text-muted">{{ $screening->auditorium->screen_label }}</p>
                                                </div>

                                                <div class="text-xs sm:text-right">
                                                    @if ($remaining === 0)
                                                        <span class="badge badge-danger">Sold out</span>
                                                    @elseif (! $bookingOpen)
                                                        <span class="badge badge-muted">Booking closed</span>
                                                    @else
                                                        <span class="text-muted"><span class="font-medium text-ink">{{ $remaining }}</span> seats left</span>
                                                    @endif
                                                </div>

                                                <div class="flex items-center justify-between gap-3 border-t border-line-soft pt-3 sm:justify-end sm:border-0 sm:pt-0">
                                                    <p class="text-xs text-muted">
                                                        from <span class="font-medium text-ink">{{ \App\Support\CinemaFormat::money($screening->base_price) }}</span>
                                                    </p>
                                                    <x-icon name="arrow-right" class="h-4 w-4 text-muted transition group-hover:translate-x-0.5 group-hover:text-gold" />
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="card p-6 lg:sticky lg:top-24" aria-labelledby="details-heading">
                <h2 id="details-heading" class="font-display text-2xl text-ink">Details</h2>
                <dl class="mt-4 divide-y divide-line-soft text-sm">
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-muted">Runtime</dt>
                        <dd class="text-ink">{{ \App\Support\CinemaFormat::duration($movie->runtime_minutes) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-muted">Language</dt>
                        <dd class="text-ink">{{ $movie->language }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-muted">Age rating</dt>
                        <dd class="text-ink">{{ $movie->age_rating }}</dd>
                    </div>
                    @if ($movie->release_date)
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-muted">Release date</dt>
                            <dd class="text-ink">{{ $movie->release_date->format('M j, Y') }}</dd>
                        </div>
                    @endif
                    @if ($movie->genres->isNotEmpty())
                        <div class="flex items-start justify-between gap-4 py-3">
                            <dt class="text-muted">Genres</dt>
                            <dd class="flex flex-wrap justify-end gap-1.5">
                                @foreach ($movie->genres as $genre)
                                    <a href="{{ route('movies.index', ['genre' => $genre->slug]) }}" class="badge badge-muted transition hover:border-gold/40 hover:text-gold">{{ $genre->name }}</a>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-5 rounded-xl border border-gold/20 bg-gold/5 p-4">
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-gold">
                        <x-icon name="shield" class="h-3.5 w-3.5" />
                        Demo checkout
                    </div>
                    <p class="mt-2 text-xs leading-5 text-muted">
                        No card details are collected and no payment is charged. Your reservation is confirmed immediately for this portfolio demo.
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
