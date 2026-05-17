@props([
    'variant' => 'neutral', // neutral | success | danger | warning | info
    'icon' => null,
])

@php
    $variantClass = $variant === 'neutral' ? '' : 'dashy-badge--' . $variant;
@endphp

<span {{ $attributes->class(['dashy-badge', $variantClass]) }}>
    @if ($icon)
        <x-dashy.icon :name="$icon" class="size-3" />
    @endif
    {{ $slot }}
</span>
