@extends('layouts.admin')

@section('title', __('admin.screenings.title'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ __('admin.screenings.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('admin.screenings.title') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('admin.screenings.schedule_showtimes') }}</p>
        </div>
        <a href="{{ route('admin.screenings.create') }}" class="btn btn-primary btn-sm w-fit">
            <x-icon name="plus" class="h-4 w-4" />
            {{ __('admin.screenings.new_screening') }}
        </a>
    </div>

    <form method="GET" action="{{ route('admin.screenings.index') }}" class="mt-6 rounded-2xl border border-line bg-surface/60 p-4">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div>
                <label for="q" class="field-label">{{ __('admin.screenings.film_search') }}</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="{{ __('admin.screenings.film_search') }}" class="field-input">
            </div>
            <div>
                <label for="date" class="field-label">{{ __('admin.screenings.date') }}</label>
                <input id="date" name="date" type="date" value="{{ $filters['date'] }}" class="field-input">
            </div>
            <div>
                <label for="movie" class="field-label">{{ __('admin.screenings.film') }}</label>
                <select id="movie" name="movie" class="field-input">
                    <option value="">{{ __('admin.status.all') }}</option>
                    @foreach ($movies as $movie)
                        <option value="{{ $movie->id }}" @selected($filters['movie'] === (string) $movie->id)>{{ $movie->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="auditorium" class="field-label">{{ __('admin.screenings.auditorium') }}</label>
                <select id="auditorium" name="auditorium" class="field-input">
                    <option value="">{{ __('admin.auditoriums.all_rooms') }}</option>
                    @foreach ($auditoriums as $auditorium)
                        <option value="{{ $auditorium->id }}" @selected($filters['auditorium'] === (string) $auditorium->id)>{{ $auditorium->code }} · {{ $auditorium->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="field-label">{{ __('admin.screenings.status') }}</label>
                <select id="status" name="status" class="field-input">
                    <option value="">{{ __('admin.status.all') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm">{{ __('admin.screenings.filter') }}</button>
                @if (array_filter($filters))
                    <a href="{{ route('admin.screenings.index') }}" class="btn btn-ghost btn-sm">{{ __('admin.genres.clear') }}</a>
                @endif
            </div>
        </div>
    </form>

    @if ($screenings->isEmpty())
        <x-empty-state class="mt-6" title="{{ __('admin.screenings.no_screenings_found') }}" description="{{ __('admin.screenings.adjust_filters_or_schedule') }}" icon="calendar">
            <a href="{{ route('admin.screenings.create') }}" class="btn btn-primary btn-sm">{{ __('admin.screenings.add_screening') }}</a>
        </x-empty-state>
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('admin.screenings.when') }}</th>
                            <th scope="col">{{ __('admin.screenings.film') }}</th>
                            <th scope="col" class="hidden md:table-cell">{{ __('admin.screenings.auditorium') }}</th>
                            <th scope="col" class="hidden lg:table-cell">{{ __('admin.screenings.price') }}</th>
                            <th scope="col">{{ __('admin.screenings.booked') }}</th>
                            <th scope="col">{{ __('admin.screenings.status') }}</th>
                            <th scope="col" class="text-right">{{ __('admin.dashboard.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($screenings as $screening)
                            @php
                                $tone = match ($screening->status) {
                                    \App\Enums\ScreeningStatus::Scheduled => 'gold',
                                    \App\Enums\ScreeningStatus::Cancelled => 'danger',
                                    \App\Enums\ScreeningStatus::Completed => 'muted',
                                };
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">
                                    <p class="font-mono text-xs text-ink">{{ \App\Support\CinemaFormat::longDate($screening->starts_at) }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted">{{ \App\Support\CinemaFormat::time($screening->starts_at) }}–{{ \App\Support\CinemaFormat::time($screening->ends_at) }}</p>
                                </td>
                                <td>
                                    <p class="max-w-48 truncate font-medium">{{ $screening->movie->title }}</p>
                                    <p class="text-[10px] text-muted">{{ $screening->movie->age_rating }} · {{ \App\Support\CinemaFormat::duration($screening->movie->runtime_minutes) }}</p>
                                </td>
                                <td class="hidden text-xs text-muted md:table-cell">{{ $screening->auditorium->code }} · {{ $screening->auditorium->name }}</td>
                                <td class="hidden whitespace-nowrap text-xs text-muted lg:table-cell">{{ \App\Support\CinemaFormat::money($screening->base_price) }}</td>
                                <td><span class="text-xs {{ $screening->reservations_count > 0 ? 'text-ink' : 'text-muted' }}">{{ $screening->reservations_count }}</span></td>
                                <td><x-status-badge :label="$screening->status->label()" :tone="$tone" /></td>
                                <td>
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('admin.screenings.edit', $screening) }}" class="btn btn-secondary btn-sm">{{ __('admin.screenings.edit') }}</a>

                                        @if ($screening->status === \App\Enums\ScreeningStatus::Scheduled)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.screenings.cancel', $screening) }}"
                                                data-confirm
                                                data-confirm-title="{{ __('admin.confirm.cancel_screening_title') }}"
                                                data-confirm-message="{{ __('admin.confirm.cancel_screening_message') }}"
                                            >
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger">{{ __('admin.screenings.cancel') }}</button>
                                            </form>
                                        @endif

                                        @if ($screening->reservations_count === 0)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.screenings.destroy', $screening) }}"
                                                data-confirm
                                                data-confirm-title="{{ __('admin.confirm.delete_screening_title') }}"
                                                data-confirm-message="{{ __('admin.confirm.delete_screening_message') }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger">{{ __('admin.screenings.delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $screenings->links() }}
    @endif
@endsection
