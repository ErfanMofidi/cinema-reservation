@extends('layouts.admin')

@section('title', __('admin.genres.edit_genre', ['name' => $genre->name]))

@section('content')
    <div class="max-w-2xl">
        <nav aria-label="Breadcrumb" class="text-xs text-muted">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('admin.genres.index') }}" class="hover:text-gold">{{ __('admin.genres.title') }}</a></li>
                <li aria-hidden="true" class="text-line">/</li>
                <li aria-current="page" class="text-ink/70">{{ $genre->name }}</li>
            </ol>
        </nav>

        <div class="mt-5">
            <p class="eyebrow">{{ __('admin.genres.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-4xl text-ink">{{ __('admin.genres.edit_a_genre') }}</h1>
        </div>

        <form method="POST" action="{{ route('admin.genres.update', $genre) }}" class="card mt-7 space-y-5 p-5 sm:p-6" novalidate>
            @csrf
            @method('PATCH')

            <x-field name="name" label="{{ __('admin.auditoriums.name') }}" :required="true">
                <input id="name" name="name" type="text" maxlength="60" value="{{ old('name', $genre->name) }}" required class="field-input" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            </x-field>

            <x-field name="slug" label="{{ __('admin.genres.slug') }}" :required="true" hint="{{ __('admin.genres.slug_changes_urls') }}">
                <input id="slug" name="slug" type="text" maxlength="80" value="{{ old('slug', $genre->slug) }}" required class="field-input" @error('slug') aria-invalid="true" aria-describedby="slug-error" @enderror>
            </x-field>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                <span class="text-xs text-muted">{{ __('admin.genres.assigned_to', ['count' => $genre->movies()->count()]) }}</span>
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.genres.index') }}" class="btn btn-secondary">{{ __('admin.auditoriums.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('admin.genres.save_changes') }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection
