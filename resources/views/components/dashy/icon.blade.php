@props([
    'name',
    'variant' => 'outline', // outline | solid | mini | micro
])

@php
    $component = match ($variant) {
        'solid' => 'heroicon-s-' . $name,
        'mini' => 'heroicon-m-' . $name,
        'micro' => 'heroicon-c-' . $name,
        default => 'heroicon-o-' . $name,
    };

    $hasSize = str_contains((string) $attributes->get('class'), 'size-');
@endphp

<x-dynamic-component
    :component="$component"
    {{ $attributes->class([
        'size-5' => ! $hasSize,
        'shrink-0',
    ]) }}
/>
