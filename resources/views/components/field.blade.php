@props([
    'name',
    'label',
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="field-label">
        {{ $label }}
        @if ($required)
            <span class="text-gold" aria-hidden="true">*</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="field-error" id="{{ $name }}-error">{{ $message }}</p>
    @enderror
</div>
