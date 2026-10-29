@extends('layouts.admin')

@section('title', __('admin.screenings.new_screening_heading'))

@section('content')
    <div class="max-w-4xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.screenings.index') }}" class="hover:text-gold">{{ __('admin.screenings.title') }}</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">{{ __('admin.screenings.new_screening_heading') }}</li>
            </ol>
        </nav>

        <div class="mt-5">
            <p class="eyebrow">{{ __('admin.screenings.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('admin.screenings.schedule_a_screening') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('admin.screenings.choose_film_auditorium_time') }}</p>
        </div>

        <div class="mt-7">
            @include('admin.screenings._form', ['action' => route('admin.screenings.store')])
        </div>
    </div>
@endsection
