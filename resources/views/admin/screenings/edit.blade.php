@extends('layouts.admin')

@section('title', 'Edit screening')

@section('content')
    <div class="max-w-4xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.screenings.index') }}" class="hover:text-gold">Screenings</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">{{ $screening->movie->title }} · {{ $screening->starts_at->format('M j, H:i') }}</li>
            </ol>
        </nav>

        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Schedule</p>
                <h1 class="mt-2 font-display text-4xl text-ink">Edit screening</h1>
                <p class="mt-2 text-sm text-muted">{{ $screening->movie->title }} · {{ $screening->auditorium->name }} · {{ $screening->reservations()->count() }} reservations</p>
            </div>
            <x-status-badge :label="$screening->status->label()" :tone="$screening->status === \App\Enums\ScreeningStatus::Scheduled ? 'gold' : 'muted'" />
        </div>

        @if ($screening->reservations()->exists())
            <x-alert type="warning" class="mt-5">
                <p class="font-medium">This screening has reservation history.</p>
                <p class="mt-1 text-xs text-warning/85">Changing the time or price does not rewrite existing reservation totals. Existing reservations keep their seat and price snapshots.</p>
            </x-alert>
        @endif

        <div class="mt-7">
            @include('admin.screenings._form', [
                'action' => route('admin.screenings.update', $screening),
                'method' => 'PATCH',
            ])
        </div>
    </div>
@endsection
