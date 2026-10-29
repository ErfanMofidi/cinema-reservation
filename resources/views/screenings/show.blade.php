@extends('layouts.app')

@section('title', __('messages.screening.choose_seats').' · '.$screening->movie->title)
@section('meta_description', __('messages.screening.choose_seats').' '.$screening->movie->title.' at '.config('cinema.name').'.')

@section('content')
    <div class="container-cinema py-8 sm:py-10">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('movies.index') }}" class="transition hover:text-gold">{{ __('messages.nav.all_films') }}</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li><a href="{{ route('movies.show', $screening->movie) }}" class="max-w-[40vw] truncate transition hover:text-gold">{{ $screening->movie->title }}</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">{{ __('messages.screening.choose_seats') }}</li>
            </ol>
        </nav>

        <div class="mt-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="eyebrow">{{ \App\Support\CinemaFormat::date($screening->starts_at) }} · {{ \App\Support\CinemaFormat::time($screening->starts_at) }}</p>
                <h1 class="mt-2 font-display text-4xl leading-none text-ink sm:text-5xl">{{ $screening->movie->title }}</h1>
                <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="calendar" class="h-3.5 w-3.5" />
                        {{ \App\Support\CinemaFormat::longDate($screening->starts_at) }}
                    </span>
                    <span class="text-line" aria-hidden="true">/</span>
                    <span>{{ \App\Support\CinemaFormat::time($screening->starts_at) }}–{{ \App\Support\CinemaFormat::time($screening->ends_at) }}</span>
                    <span class="text-line" aria-hidden="true">/</span>
                    <span>{{ $screening->auditorium->name }}</span>
                    <span class="text-line" aria-hidden="true">/</span>
                    <span>{{ $screening->movie->age_rating }}</span>
                </p>
            </div>

            <ol class="flex items-center gap-2 text-[11px] text-muted" aria-label="Booking progress">
                <li class="flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-3 py-1.5 font-semibold text-gold">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[9px] text-night">1</span>
                    {{ __('messages.screening.seats') }}
                </li>
                <li class="h-px w-6 bg-line" aria-hidden="true"></li>
                <li class="flex items-center gap-2 rounded-full border border-line px-3 py-1.5">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full border border-line text-[9px]">2</span>
                    {{ __('messages.checkout.checkout') }}
                </li>
            </ol>
        </div>

        @if (! $bookable)
            <x-alert type="warning" class="mt-6">
                <p class="font-medium">{{ __('messages.screening.online_booking_closed') }}</p>
                <p class="mt-1 text-xs text-warning/85">
                    {{ __('messages.screening.tickets_close') }}
                </p>
            </x-alert>
        @elseif ($remaining === 0)
            <x-alert type="error" class="mt-6">
                <p class="font-medium">{{ __('messages.screening.sold_out') }}</p>
                <p class="mt-1 text-xs text-danger/85">{{ __('messages.screening.sold_out') }}</p>
            </x-alert>
        @else
            <x-alert type="info" class="mt-6">
                <p class="font-medium">{{ __('messages.screening.seats_reserved_after_checkout') }}</p>
                <p class="mt-1 text-xs text-gold-soft/80">
                    {{ __('messages.screening.choose_up_to', ['max' => config('cinema.max_seats_per_reservation')]) }}
                </p>
            </x-alert>
        @endif

        <form
            method="GET"
            action="{{ route('checkout.show', $screening) }}"
            class="mt-6 grid gap-6 lg:grid-cols-[1fr_21rem] lg:items-start"
            data-seat-map
            data-endpoint="{{ route('screenings.seats', $screening) }}"
            data-checkout-url="{{ route('checkout.show', $screening) }}"
            data-max-seats="{{ config('cinema.max_seats_per_reservation') }}"
            data-currency="{{ config('cinema.currency') }}"
            data-bookable="{{ $bookable ? '1' : '0' }}"
        >
            <div class="space-y-6">
                <section class="card overflow-hidden" aria-labelledby="seat-map-heading">
                    <div class="flex flex-col gap-2 border-b border-line-soft p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 id="seat-map-heading" class="font-display text-2xl text-ink">{{ __('messages.screening.choose_seats') }}</h2>
                            <p class="mt-1 text-xs text-muted">{{ $screening->auditorium->name }} · {{ $screening->auditorium->screen_label }}</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-muted">
                            <span>{{ $remaining }} {{ \Illuminate\Support\Str::plural('seat', $remaining) }} {{ __('messages.screening.seats_available_lower') }}</span>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-full border border-line px-3 py-1.5 text-[11px] transition hover:border-gold/50 hover:text-gold"
                                data-refresh-seats
                            >
                                <x-icon name="sparkle" class="h-3 w-3" />
                                {{ __('messages.screening.refresh') }}
                            </button>
                        </div>
                    </div>

                    <div class="px-3 py-8 sm:px-6">
                        <div class="mx-auto max-w-2xl">
                            <div class="seat-screen"></div>
                            <p class="mt-2 text-center text-[9px] font-semibold uppercase tracking-[0.35em] text-muted/60">{{ __('messages.screening.screen') }}</p>
                        </div>

                        <fieldset class="mt-8">
                            <legend class="sr-only">{{ __('messages.screening.select_seats') }}</legend>

                            <div class="overflow-x-auto pb-2">
                                <div class="seat-grid" style="--seat-columns: {{ $maxColumns }}">
                                    @foreach ($rows as $rowLabel => $rowSeats)
                                        @php $rowSeatsByNumber = $rowSeats->keyBy('seat_number'); @endphp
                                        <div class="seat-row">
                                            <span class="seat-row-label" aria-hidden="true">{{ $rowLabel }}</span>

                                            @for ($seatNumber = 1; $seatNumber <= $maxColumns; $seatNumber++)
                                                @php $seat = $rowSeatsByNumber->get($seatNumber); @endphp

                                                @if ($seat)
                                                    @php
                                                        $isHeld = isset($heldLookup[$seat->id]);
                                                        $unavailable = $isHeld || ! $bookable;
                                                        $price = $screening->unitPriceFor($seat);
                                                    @endphp
                                                    <div class="relative flex justify-center">
                                                        <input
                                                            type="checkbox"
                                                            name="seats[]"
                                                            id="seat-{{ $screening->id }}-{{ $seat->id }}"
                                                            value="{{ $seat->id }}"
                                                            class="seat-input peer sr-only"
                                                            data-seat-input
                                                            data-price="{{ $price }}"
                                                            data-label="{{ $seat->label() }}"
                                                            aria-label="{{ $seat->accessibleName() }}, {{ \App\Support\CinemaFormat::money($price) }}{{ $isHeld ? ', unavailable' : '' }}{{ ! $bookable ? ', booking closed' : '' }}"
                                                            @disabled($unavailable)
                                                            @checked(! $isHeld && $bookable && in_array($seat->id, $selectedSeatIds, true))
                                                        >
                                                        <label
                                                            for="seat-{{ $screening->id }}-{{ $seat->id }}"
                                                            @class([
                                                                'seat-label',
                                                                'seat-label--premium' => $seat->seat_type === \App\Enums\SeatType::Premium,
                                                                'seat-label--accessible' => $seat->seat_type === \App\Enums\SeatType::Accessible,
                                                                'seat-label--unavailable' => $isHeld,
                                                                'seat-label--blocked' => ! $isHeld && ! $bookable,
                                                            ])
                                                            title="{{ $seat->label() }} · {{ $seat->seat_type->label() }} · {{ \App\Support\CinemaFormat::money($price) }}{{ $isHeld ? ' · unavailable' : '' }}"
                                                        >
                                                            <span aria-hidden="true">{{ $seat->seat_number }}</span>
                                                        </label>
                                                    </div>
                                                @else
                                                    <span class="seat-placeholder" aria-hidden="true"></span>
                                                @endif
                                            @endfor

                                            <span class="seat-row-label" aria-hidden="true">{{ $rowLabel }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </fieldset>

                        <p class="mt-6 text-center text-xs text-muted" data-availability-status role="status" aria-live="polite">
                            {{ __('messages.screening.availability_refreshes') }}
                        </p>
                    </div>

                    <div class="border-t border-line-soft bg-night/40 px-5 py-4">
                        <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-[11px] text-muted">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-4 w-4 rounded-t-md border border-line bg-surface-2" aria-hidden="true"></span>
                                {{ __('messages.screening.available') }}
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-4 w-4 rounded-t-md border border-gold bg-gold" aria-hidden="true"></span>
                                {{ __('messages.screening.selected') }}
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-4 w-4 rounded-t-md border border-gold/40 bg-surface-2" aria-hidden="true"></span>
                                {{ __('messages.screening.premium') }}
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-4 w-4 rounded-t-md border border-dashed border-success/60 bg-surface-2" aria-hidden="true"></span>
                                {{ __('messages.screening.accessible') }}
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="seat-label--unavailable h-4 w-4 rounded-t-md" aria-hidden="true"></span>
                                {{ __('messages.screening.unavailable') }}
                            </span>
                        </div>
                    </div>
                </section>

                @if (! $bookable || $remaining === 0)
                    <section class="card p-6" aria-labelledby="other-times-heading">
                        <h2 id="other-times-heading" class="font-display text-2xl text-ink">{{ __('messages.screening.other_times') }}</h2>
                        @if ($otherScreenings->isEmpty())
                            <p class="mt-2 text-sm text-muted">{{ __('messages.screening.no_later_screenings') }}</p>
                        @else
                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                @foreach ($otherScreenings as $other)
                                    <a href="{{ route('screenings.show', $other) }}" class="flex items-center justify-between gap-3 rounded-xl border border-line bg-night/40 px-4 py-3 text-sm transition hover:border-gold/50">
                                        <span>
                                            <span class="font-medium text-ink">{{ \App\Support\CinemaFormat::date($other->starts_at) }}</span>
                                            <span class="text-muted"> · {{ \App\Support\CinemaFormat::time($other->starts_at) }}</span>
                                        </span>
                                        <span class="text-[11px] text-muted">{{ $other->auditorium->code }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
            </div>

            <aside class="card p-5 sm:p-6 lg:sticky lg:top-24" aria-labelledby="summary-heading" data-seat-summary>
                <div class="flex items-start gap-3">
                    <img src="{{ $screening->movie->posterUrl() }}" alt="" class="h-20 w-14 shrink-0 rounded-lg border border-line object-cover" aria-hidden="true">
                    <div class="min-w-0">
                        <h2 id="summary-heading" class="font-display text-xl leading-tight text-ink">{{ $screening->movie->title }}</h2>
                        <p class="mt-1 text-[11px] leading-5 text-muted">
                            {{ \App\Support\CinemaFormat::date($screening->starts_at) }} · {{ \App\Support\CinemaFormat::time($screening->starts_at) }}<br>
                            {{ $screening->auditorium->name }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-line bg-night/50 p-4">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">{{ __('messages.screening.tickets') }}</span>
                        <span class="font-display text-2xl text-ink"><span data-seat-count>0</span><span class="ml-1 font-sans text-xs text-muted">/ {{ config('cinema.max_seats_per_reservation') }}</span></span>
                    </div>
                    <p class="mt-2 min-h-5 text-xs leading-5 text-muted" data-seat-list>{{ __('messages.screening.no_seats_selected') }}</p>
                </div>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">{{ __('messages.screening.tickets') }}</dt>
                        <dd class="text-ink" data-seat-subtotal>0 {{ config('cinema.currency') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">{{ __('messages.screening.service_fee') }}</dt>
                        <dd class="text-ink">{{ \App\Support\CinemaFormat::money(0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-line-soft pt-3">
                        <dt class="font-medium text-ink">{{ __('messages.screening.total') }}</dt>
                        <dd class="font-display text-2xl text-gold" data-seat-total>0 {{ config('cinema.currency') }}</dd>
                    </div>
                </dl>

                <p class="mt-3 min-h-5 text-xs text-danger" data-seat-message role="status" aria-live="polite"></p>

                @if ($bookable && $remaining > 0)
                    <button
                        type="submit"
                        class="btn btn-primary mt-4 w-full"
                        data-continue
                        aria-disabled="true"
                        data-pending-label="Opening checkout…"
                    >
                        <x-icon name="ticket" class="h-4 w-4" />
                        {{ __('messages.screening.continue_to_checkout') }}
                    </button>
                    <p class="mt-3 text-center text-[11px] leading-5 text-muted">
                        @auth
                            {{ __('messages.screening.seats_not_held') }}
                        @else
                            {{ __('messages.screening.youll_be_asked') }}
                        @endauth
                    </p>
                @else
                    <p class="mt-4 rounded-xl border border-line bg-night/50 px-4 py-3 text-center text-xs text-muted">
                        {{ $remaining === 0 ? __('messages.screening.sold_out') : __('messages.screening.booking_closed') }}
                    </p>
                @endif

                <div class="mt-5 flex items-start gap-2 border-t border-line-soft pt-4 text-[11px] leading-5 text-muted">
                    <x-icon name="shield" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gold" />
                    <p>{{ __('messages.screening.demo_checkout') }}</p>
                </div>
            </aside>
        </form>
    </div>
@endsection
