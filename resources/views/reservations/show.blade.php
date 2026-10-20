@extends('layouts.app')

@section('title', 'Reservation '.$reservation->reference)

@php
    $seatRows = $reservation->seats->sortBy([
        ['row_label', 'asc'],
        ['seat_number', 'asc'],
    ]);
    $isOwner = auth()->id() === $reservation->user_id;
    $tone = match ($reservation->status) {
        \App\Enums\ReservationStatus::Confirmed => 'success',
        \App\Enums\ReservationStatus::Cancelled => 'muted',
    };
    $tone = $reservation->screening->status === \App\Enums\ScreeningStatus::Cancelled ? 'danger' : $tone;
@endphp

@section('content')
    <div class="container-cinema py-8 sm:py-10">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('reservations.index') }}" class="transition hover:text-gold">My reservations</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="font-mono tracking-wider text-ink/70">{{ $reservation->reference }}</li>
            </ol>
        </nav>

        @unless ($isOwner)
            <x-alert type="info" class="mt-6">
                You are viewing this reservation as an administrator. The ticket holder is
                <span class="font-medium">{{ $reservation->user->name }}</span> ({{ $reservation->user->email }}).
            </x-alert>
        @endunless

        @if ($reservation->screening->status === \App\Enums\ScreeningStatus::Cancelled)
            <x-alert type="error" class="mt-6">
                <p class="font-medium">This screening was cancelled by the cinema.</p>
                <p class="mt-1 text-xs text-danger/85">Your reservation is kept for your records. No payment was collected in this demo.</p>
            </x-alert>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_21rem] lg:items-start">
            <article class="card overflow-hidden" aria-labelledby="ticket-heading">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft bg-night/50 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-2">
                        <x-status-badge :label="$reservation->status->label()" :tone="$tone" />
                        @if ($reservation->screening->starts_at->isFuture() && $reservation->status === \App\Enums\ReservationStatus::Confirmed)
                            <span class="badge badge-gold">Upcoming</span>
                        @endif
                    </div>
                    <p class="font-mono text-xs tracking-[0.18em] text-muted">REF · <span class="text-ink">{{ $reservation->reference }}</span></p>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                        <img
                            src="{{ $reservation->screening->movie->posterUrl() }}"
                            alt="Poster for {{ $reservation->screening->movie->title }}"
                            class="h-40 w-[6.75rem] shrink-0 rounded-xl border border-line object-cover shadow-lg shadow-black/40"
                        >
                        <div class="min-w-0">
                            <p class="eyebrow">Admit {{ $reservation->seatCount() }}</p>
                            <h1 id="ticket-heading" class="mt-2 font-display text-4xl leading-none text-ink sm:text-5xl">
                                {{ $reservation->screening->movie->title }}
                            </h1>
                            <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                                <span>{{ $reservation->screening->movie->age_rating }}</span>
                                <span class="text-line" aria-hidden="true">/</span>
                                <span>{{ \App\Support\CinemaFormat::duration($reservation->screening->movie->runtime_minutes) }}</span>
                                <span class="text-line" aria-hidden="true">/</span>
                                <span>{{ $reservation->screening->movie->language }}</span>
                            </p>
                            <p class="mt-3 text-sm text-ink/80">
                                {{ \App\Support\CinemaFormat::longDate($reservation->screening->starts_at) }}<br>
                                <span class="text-muted">{{ \App\Support\CinemaFormat::time($reservation->screening->starts_at) }}–{{ \App\Support\CinemaFormat::time($reservation->screening->ends_at) }} · {{ $reservation->screening->auditorium->name }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 border-t border-line-soft pt-6 sm:grid-cols-2">
                        <section aria-labelledby="ticket-seats-heading">
                            <h2 id="ticket-seats-heading" class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">Seats</h2>
                            <ul class="mt-3 space-y-2">
                                @foreach ($seatRows as $seat)
                                    <li class="flex items-center justify-between gap-3 rounded-lg border border-line bg-night/40 px-3 py-2 text-sm">
                                        <span class="text-ink">
                                            {{ $seat->label() }}
                                            <span class="ml-1 text-[10px] uppercase tracking-wider text-muted">{{ $seat->seat_type->label() }}</span>
                                        </span>
                                        <span class="text-xs text-muted">{{ \App\Support\CinemaFormat::money((int) $seat->pivot->unit_price) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>

                        <section aria-labelledby="ticket-total-heading">
                            <h2 id="ticket-total-heading" class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">Payment summary</h2>
                            <dl class="mt-3 space-y-2 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-muted">Tickets</dt>
                                    <dd class="text-ink">{{ \App\Support\CinemaFormat::money($reservation->subtotal) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-muted">Fees</dt>
                                    <dd class="text-ink">{{ $reservation->fees === 0 ? 'No fee' : \App\Support\CinemaFormat::money($reservation->fees) }}</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3 border-t border-line-soft pt-3">
                                    <dt class="font-medium text-ink">Total</dt>
                                    <dd class="font-display text-3xl text-gold">{{ \App\Support\CinemaFormat::money($reservation->total) }}</dd>
                                </div>
                            </dl>
                            <p class="mt-2 text-[10px] leading-4 text-muted">
                                Demo checkout — no payment was collected. Prices are stored with this reservation.
                            </p>
                        </section>
                    </div>

                    @if ($reservation->status === \App\Enums\ReservationStatus::Cancelled)
                        <div class="mt-6 rounded-xl border border-line bg-night/50 p-4">
                            <div class="flex items-start gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-line text-muted">
                                    <x-icon name="close" class="h-4 w-4" />
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-ink">Cancelled {{ $reservation->cancelled_at?->format('M j, Y · H:i') }}</p>
                                    <p class="mt-1 text-xs text-muted">{{ $reservation->cancellation_reason ?: 'This reservation was cancelled.' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </article>

            <aside class="space-y-4 lg:sticky lg:top-24">
                @if ($isOwner && $reservation->status === \App\Enums\ReservationStatus::Confirmed)
                    <section class="card p-5 sm:p-6" aria-labelledby="manage-heading">
                        <h2 id="manage-heading" class="font-display text-2xl text-ink">Need to change plans?</h2>

                        @if ($reservation->isCancellable())
                            <p class="mt-2 text-sm leading-6 text-muted">
                                You can cancel online until
                                <span class="text-ink">{{ $reservation->cancellationDeadline()->format('D, M j \a\t H:i') }}</span>.
                                Cancelling releases your seats immediately.
                            </p>
                            <form
                                method="POST"
                                action="{{ route('reservations.cancel', $reservation) }}"
                                class="mt-4"
                                data-confirm
                                data-confirm-title="Cancel this reservation?"
                                data-confirm-message="Seats {{ $reservation->seatLabelList() }} will be released immediately. This cannot be undone."
                            >
                                @csrf
                                <button type="submit" class="btn btn-danger w-full">Cancel reservation</button>
                            </form>
                        @else
                            <p class="mt-2 text-sm leading-6 text-muted">
                                @if ($reservation->screening->status !== \App\Enums\ScreeningStatus::Scheduled)
                                    Online cancellation is not available because this screening is no longer scheduled.
                                @elseif ($reservation->screening->starts_at->isPast())
                                    This screening has already started or finished, so online cancellation is closed.
                                @else
                                    Online cancellation closed at
                                    <span class="text-ink">{{ $reservation->cancellationDeadline()->format('D, M j \a\t H:i') }}</span>.
                                    Please contact the cinema if you need help.
                                @endif
                            </p>
                        @endif
                    </section>
                @endif

                <section class="card p-5 sm:p-6" aria-labelledby="visit-heading">
                    <h2 id="visit-heading" class="font-display text-2xl text-ink">Your visit</h2>
                    <ul class="mt-4 space-y-3 text-xs leading-5 text-muted">
                        <li class="flex gap-2.5">
                            <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                            Arrive at least 15 minutes early. Booking closes 15 minutes before showtime.
                        </li>
                        <li class="flex gap-2.5">
                            <x-icon name="ticket" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                            Show this reference at the door: <span class="font-mono text-ink">{{ $reservation->reference }}</span>.
                        </li>
                        <li class="flex gap-2.5">
                            <x-icon name="location" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                            {{ $reservation->screening->auditorium->name }} · {{ $reservation->screening->auditorium->screen_label }}
                        </li>
                    </ul>
                </section>

                <a href="{{ route('reservations.index') }}" class="btn btn-secondary w-full">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    All reservations
                </a>
            </aside>
        </div>
    </div>
@endsection
