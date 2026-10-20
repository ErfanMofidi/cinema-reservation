@props(['title', 'description' => null, 'icon' => 'film'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-line bg-surface/50 px-6 py-14 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full border border-line bg-surface text-muted">
        <x-icon :name="$icon" class="h-5 w-5" />
    </span>
    <h2 class="mt-5 font-display text-2xl text-ink">{{ $title }}</h2>
    @if ($description)
        <p class="mt-2 max-w-md text-sm leading-6 text-muted">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
