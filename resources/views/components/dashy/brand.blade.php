@props([
    'name' => null,
    'href' => null,
    'compact' => false,
    'logo' => null, // src
])

@php
    $tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" wire:navigate @endif
    {{ $attributes->class(['flex items-center gap-2.5']) }}
>
    @if ($logo)
        <img src="{{ $logo }}" alt="" class="size-6 shrink-0" />
    @else
        <x-app-logo-icon class="size-6 shrink-0" />
    @endif
    @if (! $compact && $name)
        <span class="font-display text-base" style="color: var(--ink);">{{ $name }}</span>
    @endif
</{{ $tag }}>
