@extends('layouts.app')

@section('title', 'Checkout · '.$screening->movie->title)

@section('content')
    <div class="container-cinema py-8 sm:py-10">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('movies.index') }}" class="transition hover:text-gold">Films</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li><a href="{{ route('movies.show', $screening->movie) }}" class="max-w-[40vw] truncate transition hover:text-gold">{{ $screening->movie->title }}</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">Checkout</li>
            </ol>
        </nav>

        <div class="mt-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="eyebrow">Almost there</p>
                <h1 class="mt-2 font-display text-4xl leading-none text-ink sm:text-5xl">Review your reservation</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">
                    Check the screening and seats below. Your total is calculated on the server and seats are rechecked when you confirm.
                </p>
            </div>

            <ol class="flex items-center gap-2 text-[11px] text-muted" aria-label="Booking progress">
                <li class="flex items-center gap-2 rounded-full border border-success/30 bg-success/10 px-3 py-1.5 font-semibold text-success">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-success text-[9px] text-night">
                        <x-icon name="check" class="h-2.5 w-2.5" />
                    </span>
                    Seats
                </li>
                <li class="h-px w-6 bg-line" aria-hidden="true"></li>
                <li class="flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-3 py-1.5 font-semibold text-gold">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[9px] text-night">2</span>
                    Checkout
                </li>
            </ol>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_22rem] lg:items-start">
            <div class="space-y-6">
                <section class="card overflow-hidden" aria-labelledby="screening-heading">
                    <div class="flex flex-col gap-5 p-5 sm:flex-row sm:p-6">
                        <img
                            src="{{ $screening->movie->posterUrl() }}"
                            alt="Poster for {{ $screening->movie->title }}"
                            class="h-36 w-24 shrink-0 rounded-xl border border-line object-cover shadow-lg shadow-black/40"
                        >
                        <div class="min-w-0">
                            <p class="eyebrow">Your screening</p>
                            <h2 id="screening-heading" class="mt-2 font-display text-3xl leading-tight text-ink">{{ $screening->movie->title }}</h2>
                            <dl class="mt-4 grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
                                <div class="flex items-center gap-2">
                                    <dt class="sr-only">Date</dt>
                                    <x-icon name="calendar" class="h-4 w-4 text-muted" />
                                    <dd class="text-ink/90">{{ \App\Support\CinemaFormat::longDate($screening->starts_at) }}</dd>
                                </div>
                                <div class="flex items-center gap-2">
                                    <dt class="sr-only">Time</dt>
                                    <x-icon name="clock" class="h-4 w-4 text-muted" />
                                    <dd class="text-ink/90">{{ \App\Support\CinemaFormat::time($screening->starts_at) }}–{{ \App\Support\CinemaFormat::time($screening->ends_at) }}</dd>
                                </div>
                                <div class="flex items-center gap-2">
                                    <dt class="sr-only">Auditorium</dt>
                                    <x-icon name="building" class="h-4 w-4 text-muted" />
                                    <dd class="text-ink/90">{{ $screening->auditorium->name }} · {{ $screening->auditorium->screen_label }}</dd>
                                </div>
                                <div class="flex items-center gap-2">
                                    <dt class="sr-only">Age rating</dt>
                                    <x-icon name="ticket" class="h-4 w-4 text-muted" />
                                    <dd class="text-ink/90">{{ $screening->movie->age_rating }} · {{ \App\Support\CinemaFormat::duration($screening->movie->runtime_minutes) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="border-t border-line-soft p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Seats</h3>
                            <span class="badge badge-gold">{{ $seats->count() }} {{ \Illuminate\Support\Str::plural('ticket', $seats->count()) }}</span>
                        </div>
                        <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($seats as $seat)
                                <li class="flex items-center justify-between gap-3 rounded-xl border border-line bg-night/40 px-4 py-2.5">
                                    <span class="flex items-center gap-2 text-sm text-ink">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gold/10 text-[10px] font-semibold text-gold">{{ $seat->seat_number }}</span>
                                        Row {{ $seat->row_label }}
                                        <span class="text-[11px] text-muted">{{ $seat->seat_type->label() }}</span>
                                    </span>
                                    <span class="text-xs text-muted">{{ \App\Support\CinemaFormat::money($screening->unitPriceFor($seat)) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="details-heading">
                    <h2 id="details-heading" class="font-display text-2xl text-ink">Booking details</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-line bg-night/40 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Ticket holder</p>
                            <p class="mt-1.5 text-sm text-ink">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-muted">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="rounded-xl border border-line bg-night/40 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Delivery</p>
                            <p class="mt-1.5 text-sm text-ink">Digital ticket in your account</p>
                            <p class="text-xs text-muted">Your reference appears immediately after confirmation.</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl border border-gold/25 bg-gold/5 p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gold/30 bg-gold/10 text-gold">
                                <x-icon name="shield" class="h-4 w-4" />
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gold">Demo checkout — no payment is collected</h3>
                                <p class="mt-1 text-xs leading-5 text-muted">
                                    This portfolio project does not ask for card details, does not contact a payment provider, and does not charge any money. Confirming creates a confirmed demo reservation immediately.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card p-5 sm:p-6" aria-labelledby="policy-heading">
                    <h2 id="policy-heading" class="font-display text-2xl text-ink">Before you confirm</h2>
                    <ul class="mt-4 space-y-3 text-sm text-muted">
                        <li class="flex gap-3">
                            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-success" />
                            <span>Your seats are secured only after this confirmation succeeds. If another customer takes one first, you will be returned to the seat map with a clear message.</span>
                        </li>
                        <li class="flex gap-3">
                            <x-icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                            <span>You can cancel online until {{ config('cinema.cancellation_cutoff_minutes') }} minutes before showtime. Cancelling releases your seats.</span>
                        </li>
                        <li class="flex gap-3">
                            <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-gold" />
                            <span>Please arrive at least 15 minutes before the screening. Online booking closes 15 minutes before start time.</span>
                        </li>
                    </ul>
                </section>
            </div>

            <aside class="card p-5 sm:p-6 lg:sticky lg:top-24" aria-labelledby="order-heading">
                <h2 id="order-heading" class="font-display text-2xl text-ink">Order summary</h2>
                <p class="mt-1 text-xs text-muted">{{ $seats->count() }} {{ \Illuminate\Support\Str::plural('seat', $seats->count()) }} · {{ $screening->auditorium->code }}</p>

                <ul class="mt-4 space-y-2 border-y border-line-soft py-4 text-sm">
                    @foreach ($seats as $seat)
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-muted">{{ $seat->label() }} <span class="text-[10px]">· {{ $seat->seat_type->label() }}</span></span>
                            <span class="text-ink/90">{{ \App\Support\CinemaFormat::money($screening->unitPriceFor($seat)) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Subtotal</dt>
                        <dd class="text-ink">{{ \App\Support\CinemaFormat::money($subtotal) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Fees</dt>
                        <dd class="text-ink">{{ $fees === 0 ? 'No fee' : \App\Support\CinemaFormat::money($fees) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3 border-t border-line-soft pt-3">
                        <dt class="font-medium text-ink">Total</dt>
                        <dd class="font-display text-3xl text-gold">{{ \App\Support\CinemaFormat::money($total) }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('checkout.store', $screening) }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">
                    @foreach ($seats as $seat)
                        <input type="hidden" name="seats[]" value="{{ $seat->id }}">
                    @endforeach

                    <button type="submit" class="btn btn-primary w-full" data-pending-label="Confirming…">
                        <x-icon name="check" class="h-4 w-4" />
                        Confirm reservation
                    </button>
                </form>

                <a
                    href="{{ route('screenings.show', ['screening' => $screening, 'seats' => $seats->pluck('id')->join(',')]) }}"
                    class="mt-2 flex w-full items-center justify-center gap-2 rounded-full px-4 py-2.5 text-xs text-muted transition hover:text-gold"
                >
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                    Change seats
                </a>

                <p class="mt-3 text-center text-[11px] leading-5 text-muted">
                    No card details. No charge. Demo reservation only.
                </p>
            </aside>
        </div>
    </div>
@endsection
