@extends('layouts.app')

@section('title', 'Now showing')

@section('content')
    @if ($featured)
        @php
            $heroImage = $featured->backdropUrl() ?? $featured->posterUrl();
        @endphp

        <section class="relative isolate overflow-hidden border-b border-line-soft">
            <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-45" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-r from-void via-void/85 to-void/30"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-void via-transparent to-void/40"></div>

            <div class="container-cinema relative py-16 sm:py-24">
                <div class="grid items-center gap-12 lg:grid-cols-[1.35fr_0.65fr]">
                    <div class="animate-fade-up">
                        <span class="badge badge-gold">Now showing</span>
                        <h1 class="mt-5 max-w-3xl font-display text-5xl leading-[0.95] text-ink sm:text-6xl lg:text-7xl">
                            {{ $featured->title }}
                        </h1>
                        @if ($featured->tagline)
                            <p class="mt-4 max-w-2xl font-display text-xl italic text-gold-soft/90 sm:text-2xl">
                                {{ $featured->tagline }}
                            </p>
                        @endif
                        <p class="mt-5 max-w-xl text-sm leading-7 text-ink/70">
                            {{ \Illuminate\Support\Str::limit($featured->synopsis, 220) }}
                        </p>

                        <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-ink/60">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="clock" class="h-3.5 w-3.5" />
                                {{ \App\Support\CinemaFormat::duration($featured->runtime_minutes) }}
                            </span>
                            <span class="text-line" aria-hidden="true">/</span>
                            <span>{{ $featured->age_rating }}</span>
                            <span class="text-line" aria-hidden="true">/</span>
                            <span>{{ $featured->language }}</span>
                            @if ($featured->genres->isNotEmpty())
                                <span class="text-line" aria-hidden="true">/</span>
                                <span>{{ $featured->genres->pluck('name')->join(' · ') }}</span>
                            @endif
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('movies.show', $featured) }}" class="btn btn-primary">
                                <x-icon name="ticket" class="h-4 w-4" />
                                Choose a showtime
                            </a>
                            <a href="{{ route('movies.index', ['showing' => 'now']) }}" class="btn btn-secondary">
                                Browse all films
                            </a>
                        </div>
                    </div>

                    <div class="hidden justify-center lg:flex">
                        <div class="relative w-64 rotate-1 transition duration-500 hover:rotate-0">
                            <div class="absolute -inset-3 rounded-3xl bg-gold/10 blur-2xl" aria-hidden="true"></div>
                            <img
                                src="{{ $featured->posterUrl() }}"
                                alt="Poster for {{ $featured->title }}"
                                class="relative aspect-[2/3] w-full rounded-2xl border border-line object-cover shadow-2xl shadow-black/70"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="container-cinema py-20">
            <p class="eyebrow">Welcome</p>
            <h1 class="mt-4 max-w-3xl font-display text-5xl leading-none text-ink sm:text-7xl">
                The lights go down.<br>The world opens.
            </h1>
            <p class="mt-5 max-w-xl text-sm leading-7 text-muted">
                {{ config('cinema.tagline') }} Browse the programme and reserve your seat in a few thoughtful steps.
            </p>
            <a href="{{ route('movies.index') }}" class="btn btn-primary mt-8">Browse films</a>
        </section>
    @endif

    @if ($tonight->isNotEmpty())
        <section class="container-cinema py-10">
            <div class="rounded-3xl border border-gold/20 bg-gold/[0.035] p-6 sm:p-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="eyebrow">Tonight</p>
                        <h2 class="mt-2 font-display text-3xl text-ink">Still time to catch a film</h2>
                    </div>
                    <p class="text-xs text-muted">{{ \App\Support\CinemaFormat::longDate(today()) }}</p>
                </div>

                <div class="mt-6 grid gap-3 md:grid-cols-3">
                    @foreach ($tonight as $screening)
                        <a
                            href="{{ route('screenings.show', $screening) }}"
                            class="group flex items-start justify-between gap-4 rounded-2xl border border-line bg-night/60 p-4 transition hover:border-gold/50"
                        >
                            <div>
                                <p class="font-mono text-sm font-semibold text-gold">{{ \App\Support\CinemaFormat::time($screening->starts_at) }}</p>
                                <h3 class="mt-1.5 text-sm font-medium text-ink group-hover:text-gold">{{ $screening->movie->title }}</h3>
                                <p class="mt-1 text-[11px] text-muted">
                                    {{ $screening->auditorium->name }}
                                    <span class="px-1 text-line">/</span>
                                    {{ max(0, $screening->auditorium->capacity - $screening->seat_holds_count) }} seats left
                                </p>
                            </div>
                            <x-icon name="arrow-right" class="mt-1 h-4 w-4 shrink-0 text-muted transition group-hover:translate-x-0.5 group-hover:text-gold" />
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($nowShowing->isNotEmpty())
        <section class="container-cinema py-12">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="eyebrow">On screen</p>
                    <h2 class="mt-2 font-display text-4xl text-ink">Now showing</h2>
                </div>
                <a href="{{ route('movies.index', ['showing' => 'now']) }}" class="group inline-flex items-center gap-2 text-sm text-muted transition hover:text-gold">
                    All showtimes
                    <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                </a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-5 lg:grid-cols-4">
                @foreach ($nowShowing as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($comingSoon->isNotEmpty())
        <section class="container-cinema py-12">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="eyebrow">On the horizon</p>
                    <h2 class="mt-2 font-display text-4xl text-ink">Coming soon</h2>
                </div>
                <a href="{{ route('movies.index', ['showing' => 'coming']) }}" class="group inline-flex items-center gap-2 text-sm text-muted transition hover:text-gold">
                    See what's ahead
                    <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                </a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-5 lg:grid-cols-3">
                @foreach ($comingSoon as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>

            <p class="mt-6 text-xs text-muted">Upcoming films open ticket sales closer to their release date.</p>
        </section>
    @endif

    <section class="container-cinema py-12">
        <div class="rounded-3xl border border-line bg-surface/70 p-6 sm:p-8">
            <div class="max-w-2xl">
                <p class="eyebrow">Plan your visit</p>
                <h2 class="mt-2 font-display text-3xl text-ink">A cinema built around the film</h2>
                <p class="mt-3 text-sm leading-6 text-muted">
                    {{ config('cinema.name') }} is a small neighbourhood cinema with reserved seating, a weekly
                    programme, and a checkout that respects your time.
                </p>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-line bg-night/50 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/30 bg-gold/10 text-gold">
                        <x-icon name="building" class="h-4 w-4" />
                    </span>
                    <h3 class="mt-4 text-sm font-semibold text-ink">Two auditoriums</h3>
                    <p class="mt-1.5 text-xs leading-5 text-muted">
                        {{ $auditoriums->map(fn ($auditorium) => $auditorium->name)->join(' and ') ?: 'Intimate rooms with reserved seating.' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-line bg-night/50 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/30 bg-gold/10 text-gold">
                        <x-icon name="calendar" class="h-4 w-4" />
                    </span>
                    <h3 class="mt-4 text-sm font-semibold text-ink">Seven days a week</h3>
                    <p class="mt-1.5 text-xs leading-5 text-muted">
                        Afternoon and evening screenings every day, with seats released online up to 15 minutes before showtime.
                    </p>
                </div>
                <div class="rounded-2xl border border-line bg-night/50 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/30 bg-gold/10 text-gold">
                        <x-icon name="shield" class="h-4 w-4" />
                    </span>
                    <h3 class="mt-4 text-sm font-semibold text-ink">Demo checkout</h3>
                    <p class="mt-1.5 text-xs leading-5 text-muted">
                        No card details are collected and no payment is charged. Reservations are confirmed immediately for this portfolio demo.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
