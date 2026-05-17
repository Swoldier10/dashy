@props([
    'text',
    'position' => 'top', // top | bottom | left | right
])

@php
    $posClass = match ($position) {
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-1',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-1',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-1',
        default => 'bottom-full left-1/2 -translate-x-1/2 mb-1',
    };
@endphp

<span
    x-data="{ show: false }"
    @mouseenter="show = true"
    @mouseleave="show = false"
    @focusin="show = true"
    @focusout="show = false"
    {{ $attributes->class(['relative inline-flex']) }}
>
    {{ $slot }}
    <span
        x-show="show"
        x-cloak
        x-transition.opacity.duration.100ms
        class="dashy-tooltip {{ $posClass }}"
    >{{ $text }}</span>
</span>
