@extends('layouts.app')

@section('title', __('messages.reservations.my_reservations'))

@section('content')
    <div class="container-cinema py-10 sm:py-14">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">{{ __('messages.reservations.your_tickets') }}</p>
                <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('messages.reservations.my_reservations') }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-muted">{{ __('messages.reservations.upcoming_screenings') }}</p>
            </div>
            <a href="{{ route('movies.index', ['showing' => 'now']) }}" class="btn btn-primary btn-sm w-fit">
                <x-icon name="film" class="h-4 w-4" />
                {{ __('messages.reservations.find_a_film') }}
            </a>
        </div>

        @if ($reservations->isEmpty())
            <x-empty-state
                class="mt-10"
                title="{{ __('messages.reservations.no_reservations_yet') }}"
                description="{{ __('messages.reservations.choose_film') }}"
                icon="ticket"
            >
                <a href="{{ route('movies.index') }}" class="btn btn-primary btn-sm">{{ __('messages.reservations.browse_programme') }}</a>
            </x-empty-state>
        @else
            <div class="mt-10 space-y-10">
                <section aria-labelledby="upcoming-heading">
                    <div class="flex items-baseline justify-between gap-4">
                        <h2 id="upcoming-heading" class="font-display text-2xl text-ink">{{ __('messages.reservations.upcoming') }}</h2>
                        <span class="text-[10px] uppercase tracking-[0.16em] text-muted">{{ __('messages.reservations.on_this_page', ['count' => $upcoming->count()]) }}</span>
                    </div>

                    @if ($upcoming->isEmpty())
                        <p class="mt-3 rounded-xl border border-dashed border-line bg-surface/40 px-4 py-5 text-sm text-muted">
                            {{ __('messages.reservations.no_upcoming_screenings') }}. Explore <a href="{{ route('movies.index', ['showing' => 'now']) }}" class="text-gold underline-offset-4 hover:underline">{{ __('messages.reservations.whats_showing_now') }}</a>.
                        </p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($upcoming as $reservation)
                                @include('reservations.card', ['reservation' => $reservation])
                            @endforeach
                        </div>
                    @endif
                </section>

                @if ($history->isNotEmpty())
                    <section aria-labelledby="history-heading">
                        <div class="flex items-baseline justify-between gap-4">
                            <h2 id="history-heading" class="font-display text-2xl text-ink">{{ __('messages.reservations.past_cancelled') }}</h2>
                            <span class="text-[10px] uppercase tracking-[0.16em] text-muted">{{ __('messages.reservations.on_this_page', ['count' => $history->count()]) }}</span>
                        </div>
                        <div class="mt-4 space-y-3 opacity-85">
                            @foreach ($history as $reservation)
                                @include('reservations.card', ['reservation' => $reservation])
                            @endforeach
                        </div>
                    </section>
                @endif

                {{ $reservations->links() }}
            @endif
    </div>
@endsection
