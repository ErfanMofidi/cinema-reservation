@php
    $tone = match ($reservation->status) {
        \App\Enums\ReservationStatus::Confirmed => 'success',
        \App\Enums\ReservationStatus::Cancelled => 'muted',
    };
    $tone = $reservation->screening->status === \App\Enums\ScreeningStatus::Cancelled ? 'danger' : $tone;
@endphp

<article class="card overflow-hidden">
    <div class="grid gap-5 p-5 sm:grid-cols-[4.5rem_1fr_auto] sm:items-center">
        <a href="{{ route('reservations.show', $reservation) }}" class="block w-20 shrink-0 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold" aria-label="View reservation {{ $reservation->reference }}">
            <img
                src="{{ $reservation->screening->movie->posterUrl() }}"
                alt="Poster for {{ $reservation->screening->movie->title }}"
                class="aspect-[2/3] w-full rounded-lg border border-line object-cover"
            >
        </a>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-status-badge :label="$reservation->status->label()" :tone="$tone" />
                <span class="font-mono text-[11px] tracking-wider text-muted">{{ $reservation->reference }}</span>
            </div>

            <h3 class="mt-2 font-display text-2xl leading-tight text-ink">
                <a href="{{ route('reservations.show', $reservation) }}" class="transition hover:text-gold">{{ $reservation->screening->movie->title }}</a>
            </h3>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="calendar" class="h-3.5 w-3.5" />
                    {{ \App\Support\CinemaFormat::date($reservation->screening->starts_at) }} · {{ \App\Support\CinemaFormat::time($reservation->screening->starts_at) }}
                </span>
                <span class="text-line" aria-hidden="true">/</span>
                <span>{{ $reservation->screening->auditorium->name }}</span>
                <span class="text-line" aria-hidden="true">/</span>
                <span>{{ $reservation->seatCount() }} {{ \Illuminate\Support\Str::plural('ticket', $reservation->seatCount()) }}</span>
            </div>

            <p class="mt-1.5 truncate text-xs text-muted/80">Seats {{ $reservation->seatLabelList() }}</p>
        </div>

        <div class="flex flex-row items-center justify-between gap-3 border-t border-line-soft pt-4 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
            <div class="text-sm sm:text-right">
                <p class="text-[10px] uppercase tracking-[0.14em] text-muted">Total</p>
                <p class="mt-0.5 font-medium text-ink">{{ \App\Support\CinemaFormat::money($reservation->total) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-secondary btn-sm">View</a>
                @can('cancel', $reservation)
                    <form
                        method="POST"
                        action="{{ route('reservations.cancel', $reservation) }}"
                        data-confirm
                        data-confirm-title="Cancel this reservation?"
                        data-confirm-message="Seats {{ $reservation->seatLabelList() }} will be released immediately. This cannot be undone."
                    >
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</article>
