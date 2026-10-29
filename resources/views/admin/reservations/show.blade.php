@extends('layouts.admin')

@section('title', __('admin.reservations.reservation', ['reference' => $reservation->reference]))

@php
    $seatRows = $reservation->seats->sortBy([
        ['row_label', 'asc'],
        ['seat_number', 'asc'],
    ]);
    $tone = match ($reservation->status) {
        \App\Enums\ReservationStatus::Confirmed => 'success',
        \App\Enums\ReservationStatus::Cancelled => 'muted',
    };
    $tone = $reservation->screening->status === \App\Enums\ScreeningStatus::Cancelled ? 'danger' : $tone;
@endphp

@section('content')
    <div class="max-w-5xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.reservations.index') }}" class="hover:text-gold">{{ __('admin.reservations.title') }}</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="font-mono tracking-wider text-ink/70">{{ $reservation->reference }}</li>
            </ol>
        </nav>

        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">{{ __('admin.reservations.booking_record') }}</p>
                <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('admin.reservations.reservation') }}</h1>
                <p class="mt-2 font-mono text-xs tracking-[0.16em] text-gold">{{ $reservation->reference }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-status-badge :label="$reservation->status->label()" :tone="$tone" />
                <span class="badge badge-muted">{{ $reservation->screening->status->label() }}</span>
            </div>
        </div>

        @if ($reservation->screening->status === \App\Enums\ScreeningStatus::Cancelled)
            <x-alert type="error" class="mt-6">
                <p class="font-medium">{{ __('admin.reservations.screening_cancelled') }}</p>
                <p class="mt-1 text-xs text-danger/85">{{ __('admin.reservations.reservation_rows_retained') }}</p>
            </x-alert>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_19rem] lg:items-start">
            <div class="space-y-6">
                <section class="card p-5 sm:p-6" aria-labelledby="booking-heading">
                    <div class="flex flex-col gap-5 sm:flex-row">
                        <img
                            src="{{ $reservation->screening->movie->posterUrl() }}"
                            alt="Poster for {{ $reservation->screening->movie->title }}"
                            class="h-36 w-24 shrink-0 rounded-xl border border-line object-cover"
                        >
                        <div class="min-w-0">
                            <h2 id="booking-heading" class="font-display text-3xl text-ink">{{ $reservation->screening->movie->title }}</h2>
                            <p class="mt-2 text-sm text-ink/80">{{ \App\Support\CinemaFormat::longDate($reservation->screening->starts_at) }}</p>
                            <p class="text-xs text-muted">{{ \App\Support\CinemaFormat::time($reservation->screening->starts_at) }}–{{ \App\Support\CinemaFormat::time($reservation->screening->ends_at) }} · {{ $reservation->screening->auditorium->name }} ({{ $reservation->screening->auditorium->code }})</p>
                            <p class="mt-2 text-xs text-muted">{{ __('admin.reservations.screening_number', ['id' => $reservation->screening_id]) }} · {{ __('admin.reservations.seats_count', ['count' => $reservation->seatCount()]) }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 border-t border-line-soft pt-5 sm:grid-cols-2">
                        <section aria-labelledby="seats-heading">
                            <h3 id="seats-heading" class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">{{ __('admin.reservations.seats_price_snapshots') }}</h3>
                            <ul class="mt-3 space-y-2">
                                @foreach ($seatRows as $seat)
                                    <li class="flex items-center justify-between gap-3 rounded-lg border border-line bg-night/40 px-3 py-2 text-sm">
                                        <span>{{ $seat->label() }} <span class="ml-1 text-[10px] uppercase tracking-wider text-muted">{{ $seat->seat_type->label() }}</span></span>
                                        <span class="text-xs text-muted">{{ \App\Support\CinemaFormat::money((int) $seat->pivot->unit_price) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>

                        <section aria-labelledby="totals-heading">
                            <h3 id="totals-heading" class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">{{ __('admin.reservations.stored_totals') }}</h3>
                            <dl class="mt-3 space-y-2 text-sm">
                                <div class="flex justify-between gap-3"><dt class="text-muted">{{ __('admin.reservations.subtotal') }}</dt><dd>{{ \App\Support\CinemaFormat::money($reservation->subtotal) }}</dd></div>
                                <div class="flex justify-between gap-3"><dt class="text-muted">{{ __('admin.reservations.fees') }}</dt><dd>{{ \App\Support\CinemaFormat::money($reservation->fees) }}</dd></div>
                                <div class="flex justify-between gap-3 border-t border-line-soft pt-3"><dt class="font-medium text-ink">{{ __('admin.reservations.total_amount') }}</dt><dd class="font-display text-2xl text-gold">{{ \App\Support\CinemaFormat::money($reservation->total) }}</dd></div>
                                <div class="flex justify-between gap-3"><dt class="text-muted">{{ __('admin.reservations.currency') }}</dt><dd>{{ $reservation->currency }}</dd></div>
                            </dl>
                        </section>
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="timeline-heading">
                    <h2 id="timeline-heading" class="font-display text-2xl text-ink">{{ __('admin.reservations.record_timeline') }}</h2>
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-[10px] uppercase tracking-[0.14em] text-muted">{{ __('admin.reservations.created') }}</dt>
                            <dd class="mt-1 text-ink/85">{{ \App\Support\CinemaFormat::longDate($reservation->created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase tracking-[0.14em] text-muted">{{ __('admin.reservations.confirmed') }}</dt>
                            <dd class="mt-1 text-ink/85">{{ $reservation->confirmed_at?->format('M j, Y · H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase tracking-[0.14em] text-muted">{{ __('admin.reservations.cancelled') }}</dt>
                            <dd class="mt-1 text-ink/85">{{ $reservation->cancelled_at?->format('M j, Y · H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase tracking-[0.14em] text-muted">{{ __('admin.reservations.cancellation_reason') }}</dt>
                            <dd class="mt-1 text-ink/85">{{ $reservation->cancellation_reason ?: '—' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="card p-5" aria-labelledby="customer-heading">
                    <h2 id="customer-heading" class="font-display text-2xl text-ink">{{ __('admin.reservations.customer_info') }}</h2>
                    <p class="mt-3 text-sm font-medium text-ink">{{ $reservation->user->name }}</p>
                    <p class="mt-0.5 break-all text-xs text-muted">{{ $reservation->user->email }}</p>
                    <p class="mt-3 text-[10px] uppercase tracking-[0.14em] text-muted">{{ __('admin.reservations.user_number', ['id' => $reservation->user_id]) }}</p>
                </section>

                <section class="card p-5" aria-labelledby="links-heading">
                    <h2 id="links-heading" class="font-display text-2xl text-ink">{{ __('admin.reservations.related') }}</h2>
                    <div class="mt-3 space-y-2">
                        <a href="{{ route('admin.screenings.edit', $reservation->screening) }}" class="flex items-center justify-between gap-2 rounded-lg border border-line bg-night/40 px-3 py-2 text-xs text-muted transition hover:border-gold/40 hover:text-gold">
                            {{ __('admin.reservations.edit_screening') }}
                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                        <a href="{{ route('admin.reservations.index', ['q' => $reservation->user->email]) }}" class="flex items-center justify-between gap-2 rounded-lg border border-line bg-night/40 px-3 py-2 text-xs text-muted transition hover:border-gold/40 hover:text-gold">
                            {{ __('admin.reservations.customers_reservations') }}
                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                    <p class="mt-4 text-[11px] leading-5 text-muted">{{ __('admin.reservations.demo_record') }}</p>
                </section>

                <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary w-full">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('admin.reservations.all_reservations') }}
                </a>
            </aside>
        </div>
    </div>
@endsection
