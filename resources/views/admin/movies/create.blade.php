@extends('layouts.admin')

@section('title', __('admin.movies.new_film_heading'))

@section('content')
    <div class="max-w-5xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.movies.index') }}" class="hover:text-gold">{{ __('admin.movies.title') }}</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">{{ __('admin.movies.new_film_heading') }}</li>
            </ol>
        </nav>

        <div class="mt-5">
            <p class="eyebrow">{{ __('admin.movies.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('admin.movies.add_a_film') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('admin.movies.create_public_page') }}</p>
        </div>

        <div class="mt-7">
            @include('admin.movies._form', ['action' => route('admin.movies.store')])
        </div>
    </div>
@endsection
