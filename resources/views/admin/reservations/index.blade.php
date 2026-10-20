@extends('layouts.admin')

@section('title', 'Reservations')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Bookings</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Reservations</h1>
            <p class="mt-2 text-sm text-muted">Inspect bookings, seat assignments, statuses, and stored totals. Reservation history is read-only.</p>
        </div>
        <span class="badge badge-muted w-fit">{{ $reservations->total() }} total</span>
    </div>

    <form method="GET" action="{{ route('admin.reservations.index') }}" class="mt-6 flex flex-col gap-3 rounded-2xl border border-line bg-surface/60 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="field-label">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Reference, customer name, or email" class="field-input">
        </div>
        <div class="sm:w-44">
            <label for="status" class="field-label">Status</label>
            <select id="status" name="status" class="field-input">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-44">
            <label for="date" class="field-label">Screening date</label>
            <input id="date" name="date" type="date" value="{{ $filters['date'] }}" class="field-input">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if ($filters['q'] !== '' || $filters['status'] !== '' || $filters['date'] !== '')
                <a href="{{ route('admin.reservations.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </div>
    </form>

    @if ($reservations->isEmpty())
        <x-empty-state class="mt-6" title="No reservations found" description="Adjust the filters or wait for the first booking." icon="ticket" />
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Customer</th>
                            <th scope="col" class="hidden lg:table-cell">Screening</th>
                            <th scope="col" class="hidden md:table-cell">Seats</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="hidden xl:table-cell">Booked</th>
                            <th scope="col" class="text-right">View</th>
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
                                    <p class="text-[10px] text-muted">{{ $reservation->screening->starts_at->format('M j, Y · H:i') }}</p>
                                </td>
                                <td class="hidden max-w-44 md:table-cell">
                                    <p class="truncate text-xs text-muted">{{ $reservation->seatLabelList() }}</p>
                                    <p class="text-[10px] text-muted/70">{{ $reservation->seatCount() }} {{ \Illuminate\Support\Str::plural('ticket', $reservation->seatCount()) }}</p>
                                </td>
                                <td class="whitespace-nowrap text-xs text-ink">{{ \App\Support\CinemaFormat::money($reservation->total) }}</td>
                                <td><x-status-badge :label="$reservation->status->label()" :tone="$tone" /></td>
                                <td class="hidden whitespace-nowrap text-[10px] text-muted xl:table-cell">{{ $reservation->created_at->format('M j, Y H:i') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="btn btn-secondary btn-sm">Open</a>
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
