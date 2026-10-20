@props(['label', 'tone' => 'muted'])

<span {{ $attributes->merge(['class' => "badge badge-{$tone}"]) }}>{{ $label }}</span>
