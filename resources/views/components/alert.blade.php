@props(['type' => 'info', 'dismissible' => false])

@php
    $styles = match ($type) {
        'success' => 'border-success/30 bg-success/10 text-success',
        'error', 'danger' => 'border-danger/30 bg-danger/10 text-danger',
        'warning' => 'border-warning/30 bg-warning/10 text-warning',
        default => 'border-gold/25 bg-gold/5 text-gold-soft',
    };
    $icon = match ($type) {
        'success' => 'check',
        'error', 'danger' => 'alert',
        'warning' => 'alert',
        default => 'info',
    };
@endphp

<div
    {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {$styles}", 'role' => $type === 'error' ? 'alert' : 'status']) }}
>
    <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0" />
    <div class="min-w-0 flex-1">{{ $slot }}</div>
    @if ($dismissible)
        <button type="button" class="shrink-0 rounded p-1 opacity-70 hover:opacity-100" data-dismiss-alert aria-label="Dismiss message">
            <x-icon name="close" class="h-4 w-4" />
        </button>
    @endif
</div>
