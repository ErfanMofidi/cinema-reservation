@extends('layouts.admin')

@section('title', __('admin.title.dashboard'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ __('admin.dashboard.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('admin.dashboard.title') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('admin.dashboard.greeting', ['name' => \Illuminate\Support\Str::before(auth()->user()->name, ' ')]) }} · {{ __('admin.dashboard.snapshot') }}</p>
        </div>
        <a href="{{ route('admin.screenings.create') }}" class="btn btn-primary btn-sm w-fit">
            <x-icon name="plus" class="h-4 w-4" />
            {{ __('admin.dashboard.add_screening') }}
        </a>
    </div>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="metric-card">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">{{ __('admin.dashboard.published_films') }}</p>
            <p class="metric-value mt-3">{{ $publishedMovies }}</p>
            <a href="{{ route('admin.movies.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-[11px] text-gold hover:underline">{{ __('admin.dashboard.manage_catalogue') }} <x-icon name="arrow-right" class="h-3 w-3" /></a>
        </div>
        <div class="metric-card">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">{{ __('admin.dashboard.upcoming_screenings') }}</p>
            <p class="metric-value mt-3">{{ $upcomingScreenings }}</p>
            <a href="{{ route('admin.screenings.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-[11px] text-gold hover:underline">{{ __('admin.dashboard.open_schedule') }} <x-icon name="arrow-right" class="h-3 w-3" /></a>
        </div>
        <div class="metric-card">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">{{ __('admin.dashboard.confirmed_bookings') }}</p>
            <p class="metric-value mt-3">{{ $confirmedReservations }}</p>
            <a href="{{ route('admin.reservations.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-[11px] text-gold hover:underline">{{ __('admin.dashboard.inspect_bookings') }} <x-icon name="arrow-right" class="h-3 w-3" /></a>
        </div>
        <div class="metric-card">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">{{ __('admin.dashboard.seats_reserved') }}</p>
            <p class="metric-value mt-3">{{ $seatsSold }}</p>
            <p class="mt-3 text-[11px] text-muted">{{ __('admin.dashboard.demo_value', ['value' => \App\Support\CinemaFormat::money($reservationValue)]) }} · {{ __('admin.dashboard.no_payment_collected') }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <section class="card overflow-hidden" aria-labelledby="today-screenings-heading">
            <div class="flex items-center justify-between gap-4 border-b border-line-soft p-5">
                <div>
                    <h2 id="today-screenings-heading" class="font-display text-2xl text-ink">{{ __('admin.dashboard.todays_screenings') }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ __('admin.dashboard.scheduled_showings', ['date' => today()->format('F j')]) }}</p>
                </div>
                <a href="{{ route('admin.screenings.index', ['date' => today()->toDateString()]) }}" class="text-xs text-gold hover:underline">{{ __('admin.dashboard.view_all') }}</a>
            </div>

            @if ($todaysScreenings->isEmpty())
                <x-empty-state
                    class="m-5 border-0 bg-transparent py-10"
                    title="{{ __('admin.dashboard.no_screenings_today') }}"
                    description="{{ __('admin.dashboard.add_screening_or_choose_date') }}"
                    icon="calendar"
                >
                    <a href="{{ route('admin.screenings.create') }}" class="btn btn-secondary btn-sm">{{ __('admin.dashboard.add_screening') }}</a>
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('admin.dashboard.time') }}</th>
                                <th scope="col">{{ __('admin.dashboard.film') }}</th>
                                <th scope="col">{{ __('admin.dashboard.auditorium') }}</th>
                                <th scope="col">{{ __('admin.dashboard.seats_held') }}</th>
                                <th scope="col"><span class="sr-only">{{ __('admin.dashboard.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($todaysScreenings as $screening)
                                <tr>
                                    <td class="whitespace-nowrap font-mono text-xs text-gold">{{ \App\Support\CinemaFormat::time($screening->starts_at) }}</td>
                                    <td>
                                        <p class="max-w-52 truncate font-medium">{{ $screening->movie->title }}</p>
                                        <p class="text-[11px] text-muted">until {{ \App\Support\CinemaFormat::time($screening->ends_at) }}</p>
                                    </td>
                                    <td class="text-xs text-muted">{{ $screening->auditorium->code }}</td>
                                    <td class="text-xs">{{ $screening->seat_holds_count }} / {{ $screening->auditorium->capacity }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.screenings.edit', $screening) }}" class="text-xs text-muted hover:text-gold">{{ __('admin.dashboard.edit') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card overflow-hidden" aria-labelledby="recent-bookings-heading">
            <div class="flex items-center justify-between gap-4 border-b border-line-soft p-5">
                <div>
                    <h2 id="recent-bookings-heading" class="font-display text-2xl text-ink">{{ __('admin.dashboard.recent_bookings') }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ __('admin.dashboard.latest_reservations') }}</p>
                </div>
                <a href="{{ route('admin.reservations.index') }}" class="text-xs text-gold hover:underline">{{ __('admin.dashboard.view_all') }}</a>
            </div>

            @if ($recentReservations->isEmpty())
                <p class="p-5 text-sm text-muted">{{ __('admin.dashboard.no_reservations_yet') }}</p>
            @else
                <ul class="divide-y divide-line-soft">
                    @foreach ($recentReservations as $reservation)
                        <li>
                            <a href="{{ route('admin.reservations.show', $reservation) }}" class="flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-surface-2/60">
                                <div class="min-w-0">
                                    <p class="font-mono text-[11px] tracking-wider text-gold">{{ $reservation->reference }}</p>
                                    <p class="mt-0.5 truncate text-sm text-ink">{{ $reservation->screening->movie->title }}</p>
                                    <p class="truncate text-[11px] text-muted">{{ $reservation->user->name }} · {{ $reservation->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <x-status-badge
                                        :label="$reservation->status->label()"
                                        :tone="$reservation->status === \App\Enums\ReservationStatus::Confirmed ? 'success' : 'muted'"
                                    />
                                    <p class="mt-1 text-[11px] text-muted">{{ \App\Support\CinemaFormat::money($reservation->total) }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
