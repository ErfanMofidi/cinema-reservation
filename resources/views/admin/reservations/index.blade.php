@extends('layouts.admin')

@section('title', __('admin.reservations.title'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ __('admin.reservations.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('admin.reservations.title') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('admin.reservations.inspect_bookings') }}</p>
        </div>
        <span class="badge badge-muted w-fit">{{ __('admin.reservations.total', ['total' => $reservations->total()]) }}</span>
    </div>

    <form method="GET" action="{{ route('admin.reservations.index') }}" class="mt-6 flex flex-col gap-3 rounded-2xl border border-line bg-surface/60 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="field-label">{{ __('admin.reservations.search') }}</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="{{ __('admin.reservations.reference_customer_email') }}" class="field-input">
        </div>
        <div class="sm:w-44">
            <label for="status" class="field-label">{{ __('admin.reservations.status') }}</label>
            <select id="status" name="status" class="field-input">
                <option value="">{{ __('admin.status.all') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-44">
            <label for="date" class="field-label">{{ __('admin.reservations.screening_date') }}</label>
            <input id="date" name="date" type="date" value="{{ $filters['date'] }}" class="field-input">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('admin.genres.filter') }}</button>
            @if ($filters['q'] !== '' || $filters['status'] !== '' || $filters['date'] !== '')
                <a href="{{ route('admin.reservations.index') }}" class="btn btn-ghost btn-sm">{{ __('admin.genres.clear') }}</a>
            @endif
        </div>
    </form>

    @if ($reservations->isEmpty())
        <x-empty-state class="mt-6" title="{{ __('admin.reservations.no_reservations_found') }}" description="{{ __('admin.reservations.adjust_filters_or_wait') }}" icon="ticket" />
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('admin.reservations.reference') }}</th>
                            <th scope="col">{{ __('admin.reservations.customer') }}</th>
                            <th scope="col" class="hidden lg:table-cell">{{ __('admin.reservations.screening') }}</th>
                            <th scope="col" class="hidden md:table-cell">{{ __('admin.reservations.seats') }}</th>
                            <th scope="col">{{ __('admin.reservations.total_amount') }}</th>
                            <th scope="col">{{ __('admin.reservations.status') }}</th>
                            <th scope="col" class="hidden xl:table-cell">{{ __('admin.reservations.booked_at') }}</th>
                            <th scope="col" class="text-right">{{ __('admin.reservations.view') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reservations as $reservation)
                            @php
                                $tone = $reservation->status === \App\Enums\ReservationStatus::Confirmed ? 'success' : 'muted';
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap font-mono text-[11px] tracking-wider text-gold">{{ $reservation->reference }}</td>
                                <td>
                                    <p class="max-w-40 truncate text-sm">{{ $reservation->user->name }}</p>
                                    <p class="max-w-40 truncate text-[10px] text-muted">{{ $reservation->user->email }}</p>
                                </td>
                                <td class="hidden lg:table-cell">
                                    <p class="max-w-48 truncate text-xs text-ink">{{ $reservation->screening->movie->title }}</p>
                                    <p class="text-[10px] text-muted">{{ \App\Support\CinemaFormat::longDate($reservation->screening->starts_at) }}</p>
                                </td>
                                <td class="hidden max-w-44 md:table-cell">
                                    <p class="truncate text-xs text-muted">{{ $reservation->seatLabelList() }}</p>
                                    <p class="text-[10px] text-muted/70">{{ $reservation->seatCount() }} {{ \Illuminate\Support\Str::plural('ticket', $reservation->seatCount()) }}</p>
                                </td>
                                <td class="whitespace-nowrap text-xs text-ink">{{ \App\Support\CinemaFormat::money($reservation->total) }}</td>
                                <td><x-status-badge :label="$reservation->status->label()" :tone="$tone" /></td>
                                <td class="hidden whitespace-nowrap text-[10px] text-muted xl:table-cell">{{ \App\Support\CinemaFormat::longDate($reservation->created_at) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="btn btn-secondary btn-sm">{{ __('admin.reservations.open') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $reservations->links() }}
    @endif
@endsection
