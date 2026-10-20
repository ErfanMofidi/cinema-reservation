@extends('layouts.admin')

@section('title', 'New screening')

@section('content')
    <div class="max-w-4xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.screenings.index') }}" class="hover:text-gold">Screenings</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">New screening</li>
            </ol>
        </nav>

        <div class="mt-5">
            <p class="eyebrow">Schedule</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Schedule a screening</h1>
            <p class="mt-2 text-sm text-muted">Choose a published film, an active auditorium, and a time that does not conflict with the existing schedule.</p>
        </div>

        <div class="mt-7">
            @include('admin.screenings._form', ['action' => route('admin.screenings.store')])
        </div>
    </div>
@endsection
