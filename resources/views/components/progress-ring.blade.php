@props(['percent' => 0, 'color' => 'primary'])

@php
    $colorClass = match ($color) {
        'tertiary' => 'stroke-tertiary',
        'error' => 'stroke-error',
        default => 'stroke-primary',
    };

    $textColorClass = match ($color) {
        'tertiary' => 'text-tertiary',
        'error' => 'text-error',
        default => 'text-primary',
    };
@endphp

<div {{ $attributes->merge(['class' => 'relative w-16 h-16 flex items-center justify-center']) }}>
    <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
        <circle class="stroke-surface-variant" cx="18" cy="18" fill="none" r="16" stroke-width="3" />
        <circle class="{{ $colorClass }}" cx="18" cy="18" fill="none" r="16" stroke-dasharray="100 100" stroke-dashoffset="{{ 100 - $percent }}" stroke-linecap="round" stroke-width="3" />
    </svg>
    <span class="absolute font-mono text-label-sm font-bold {{ $textColorClass }}">{{ $percent }}%</span>
</div>
