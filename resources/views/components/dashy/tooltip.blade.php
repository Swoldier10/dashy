@props([
    'text',
    'position' => 'top',  // top | bottom | left | right
    'align' => 'center',  // start | center | end (only used for top/bottom)
])

@php
    $vertical = in_array($position, ['top', 'bottom'], true);
    $horizontalAnchor = match ($align) {
        'start' => 'left-0',
        'end' => 'right-0',
        default => 'left-1/2 -translate-x-1/2',
    };
    $verticalAnchor = match ($align) {
        'start' => 'top-0',
        'end' => 'bottom-0',
        default => 'top-1/2 -translate-y-1/2',
    };

    $posClass = match ($position) {
        'bottom' => 'top-full mt-1 ' . $horizontalAnchor,
        'left' => 'right-full mr-1 ' . $verticalAnchor,
        'right' => 'left-full ml-1 ' . $verticalAnchor,
        default => 'bottom-full mb-1 ' . $horizontalAnchor,
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
