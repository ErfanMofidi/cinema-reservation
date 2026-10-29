@extends('layouts.admin')

@section('title', __('admin.genres.title'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">{{ __('admin.genres.eyebrow') }}</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">{{ __('admin.genres.title') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('admin.genres.organise_programme') }}</p>
        </div>
        <a href="{{ route('admin.genres.create') }}" class="btn btn-primary btn-sm w-fit">
            <x-icon name="plus" class="h-4 w-4" />
            {{ __('admin.genres.new_genre') }}
        </a>
    </div>

    <form method="GET" action="{{ route('admin.genres.index') }}" class="mt-6 flex flex-col gap-3 rounded-2xl border border-line bg-surface/60 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="field-label">{{ __('admin.genres.search_genres') }}</label>
            <input id="q" name="q" type="search" value="{{ $search }}" placeholder="{{ __('admin.genres.search_genres') }}" class="field-input">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('admin.genres.filter') }}</button>
            @if ($search !== '')
                <a href="{{ route('admin.genres.index') }}" class="btn btn-ghost btn-sm">{{ __('admin.genres.clear') }}</a>
            @endif
        </div>
    </form>

    @if ($genres->isEmpty())
        <x-empty-state class="mt-6" title="{{ __('admin.genres.no_genres_found') }}" description="{{ __('admin.genres.add_genre_to_classify') }}" icon="sparkle">
            <a href="{{ route('admin.genres.create') }}" class="btn btn-primary btn-sm">{{ __('admin.genres.add_genre') }}</a>
        </x-empty-state>
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('admin.genres.genre') }}</th>
                            <th scope="col">{{ __('admin.genres.slug') }}</th>
                            <th scope="col">{{ __('admin.genres.films') }}</th>
                            <th scope="col" class="text-right">{{ __('admin.dashboard.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($genres as $genre)
                            <tr>
                                <td class="font-medium text-ink">{{ $genre->name }}</td>
                                <td><code class="rounded bg-night px-1.5 py-0.5 font-mono text-[11px] text-muted">{{ $genre->slug }}</code></td>
                                <td>
                                    <span class="{{ $genre->movies_count > 0 ? 'text-ink' : 'text-muted' }} text-xs">{{ $genre->movies_count }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-secondary btn-sm">{{ __('admin.genres.edit') }}</a>
                                        @if ($genre->movies_count === 0)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.genres.destroy', $genre) }}"
                                                data-confirm
                                                data-confirm-title="{{ __('admin.confirm.delete_genre_title', ['name' => $genre->name]) }}"
                                                data-confirm-message="{{ __('admin.confirm.delete_genre_message') }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger">{{ __('admin.genres.delete') }}</button>
                                            </form>
                                        @else
                                            <span class="text-[10px] uppercase tracking-wider text-muted/60">{{ __('admin.genres.in_use') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $genres->links() }}
    @endif
@endsection
