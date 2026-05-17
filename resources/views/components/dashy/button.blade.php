@props([
    'variant' => 'primary',     // primary | filled | ghost | danger | cocoa
    'size' => 'md',             // sm | md | lg
    'icon' => null,             // heroicon name, leading
    'iconTrailing' => null,     // heroicon name, trailing
    'iconVariant' => 'outline', // outline | solid | mini | micro
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'block' => false,           // full-width
])

@php
    $classes = [
        'dashy-btn',
        'dashy-btn--' . $variant,
        'dashy-btn--' . $size,
        $block ? 'dashy-btn--block' : '',
    ];

    $iconSize = match ($size) {
        'sm' => 'size-3.5',
        'lg' => 'size-5',
        default => 'size-4',
    };

    $isDisabled = $disabled || $loading;
@endphp

@if ($href && ! $isDisabled)
    <a
        href="{{ $href }}"
        {{ $attributes->class($classes) }}
    >
        @if ($loading)
            <x-dashy.icon-loading :class="$iconSize" />
        @elseif ($icon)
            <x-dashy.icon :name="$icon" :variant="$iconVariant" :class="$iconSize" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconTrailing)
            <x-dashy.icon :name="$iconTrailing" :variant="$iconVariant" :class="$iconSize" />
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @if ($isDisabled) disabled aria-disabled="true" @endif
        {{ $attributes->class($classes) }}
    >
        @if ($loading)
            <x-dashy.icon-loading :class="$iconSize" />
        @elseif ($icon)
            <x-dashy.icon :name="$icon" :variant="$iconVariant" :class="$iconSize" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconTrailing)
            <x-dashy.icon :name="$iconTrailing" :variant="$iconVariant" :class="$iconSize" />
        @endif
    </button>
@endif
