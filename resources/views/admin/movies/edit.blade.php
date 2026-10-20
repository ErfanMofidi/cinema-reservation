@extends('layouts.admin')

@section('title', 'Edit '.$movie->title)

@section('content')
    <div class="max-w-5xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.movies.index') }}" class="hover:text-gold">Films</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="max-w-[60vw] truncate text-ink/70">{{ $movie->title }}</li>
            </ol>
        </nav>

        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Catalogue</p>
                <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Edit film</h1>
                <p class="mt-2 text-sm text-muted">{{ $movie->title }} · {{ $movie->slug }}</p>
            </div>
            <a href="{{ route('movies.show', $movie) }}" class="btn btn-secondary btn-sm w-fit" target="_blank" rel="noopener">
                <x-icon name="external" class="h-3.5 w-3.5" />
                View public page
            </a>
        </div>

        <div class="mt-7">
            @include('admin.movies._form', [
                'action' => route('admin.movies.update', $movie),
                'method' => 'PATCH',
            ])
        </div>
    </div>
@endsection
