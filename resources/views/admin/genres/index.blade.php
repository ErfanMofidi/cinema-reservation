@extends('layouts.admin')

@section('title', 'Genres')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Catalogue</p>
            <h1 class="mt-2 font-display text-4xl text-ink sm:text-5xl">Genres</h1>
            <p class="mt-2 text-sm text-muted">Use genres to organise the programme and power public filters.</p>
        </div>
        <a href="{{ route('admin.genres.create') }}" class="btn btn-primary btn-sm w-fit">
            <x-icon name="plus" class="h-4 w-4" />
            New genre
        </a>
    </div>

    <form method="GET" action="{{ route('admin.genres.index') }}" class="mt-6 flex flex-col gap-3 rounded-2xl border border-line bg-surface/60 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="field-label">Search genres</label>
            <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Genre name" class="field-input">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if ($search !== '')
                <a href="{{ route('admin.genres.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </div>
    </form>

    @if ($genres->isEmpty())
        <x-empty-state class="mt-6" title="No genres found" description="Add a genre to start classifying films." icon="sparkle">
            <a href="{{ route('admin.genres.create') }}" class="btn btn-primary btn-sm">Add genre</a>
        </x-empty-state>
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Genre</th>
                            <th scope="col">Slug</th>
                            <th scope="col">Films</th>
                            <th scope="col" class="text-right">Actions</th>
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
                                        <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        @if ($genre->movies_count === 0)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.genres.destroy', $genre) }}"
                                                data-confirm
                                                data-confirm-title="Delete {{ $genre->name }}?"
                                                data-confirm-message="This genre is not assigned to any films and will be permanently removed."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger">Delete</button>
                                            </form>
                                        @else
                                            <span class="text-[10px] uppercase tracking-wider text-muted/60">In use</span>
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
