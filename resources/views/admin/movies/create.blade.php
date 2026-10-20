@extends('layouts.admin')

@section('title', 'New film')

@section('content')
    <div class="max-w-5xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.movies.index') }}" class="hover:text-gold">Films</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">New film</li>
            </ol>
        </nav>

        <div class="mt-5">
            <p class="eyebrow">Catalogue</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Add a film</h1>
            <p class="mt-2 text-sm text-muted">Create the public film page, then schedule screenings from the screenings section.</p>
        </div>

        <div class="mt-7">
            @include('admin.movies._form', ['action' => route('admin.movies.store')])
        </div>
    </div>
@endsection
