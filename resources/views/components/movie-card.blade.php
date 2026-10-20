@props(['movie'])

@php
    $isUpcoming = $movie->release_date?->isFuture() ?? false;
@endphp

<article class="group">
    <a href="{{ route('movies.show', $movie) }}" class="block rounded-2xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold">
        <div class="relative aspect-[2/3] overflow-hidden rounded-2xl border border-line bg-surface shadow-[0_18px_60px_-30px_rgb(0_0_0_/_0.9)]">
            <img
                src="{{ $movie->posterUrl() }}"
                alt="Poster for {{ $movie->title }}"
                loading="lazy"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.035]"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-void via-void/10 to-transparent opacity-80"></div>

            <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3">
                <span class="badge border-void/30 bg-void/70 text-ink backdrop-blur">{{ $movie->age_rating }}</span>
                @if ($isUpcoming)
                    <span class="badge badge-gold">Coming soon</span>
                @endif
            </div>

            <div class="absolute inset-x-0 bottom-0 p-4">
                <p class="text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                    {{ $movie->genres->pluck('name')->join(' · ') }}
                </p>
            </div>
        </div>

        <div class="mt-3 px-0.5">
            <h3 class="font-display text-xl leading-tight text-ink transition group-hover:text-gold">{{ $movie->title }}</h3>
            <p class="mt-1 text-xs text-muted">
                {{ \App\Support\CinemaFormat::duration($movie->runtime_minutes) }}
                <span class="px-1 text-line">/</span>
                {{ $movie->language }}
            </p>
        </div>
    </a>
</article>
