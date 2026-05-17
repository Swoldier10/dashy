@props([
    'variant' => 'default', // default | subtle
    'color' => null,        // null | error | success | warning | muted
    'size' => 'sm',         // xs | sm | md
    'as' => 'p',
])

@php
    $sizeClass = match ($size) {
        'xs' => 'text-xs',
        'md' => 'text-base',
        default => 'text-sm',
    };

    $colorStyle = match ($color) {
        'error' => 'color: var(--state-error);',
        'success' => 'color: var(--state-success);',
        'warning' => 'color: var(--state-warning);',
        'muted', 'subtle' => 'color: var(--ink-muted);',
        default => $variant === 'subtle' ? 'color: var(--ink-muted);' : 'color: var(--ink);',
    };
@endphp

<{{ $as }}
    {{ $attributes->class(['leading-relaxed', $sizeClass]) }}
    style="{{ $colorStyle }} {{ $attributes->get('style') }}"
>
    {{ $slot }}
</{{ $as }}>
