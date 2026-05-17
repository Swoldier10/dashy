@props([
    'href' => '#',
    'variant' => 'default', // default | subtle | underline
    'wireNavigate' => false,
])

@php
    $base = 'transition-colors';
    $variantClass = match ($variant) {
        'subtle' => 'underline underline-offset-4 decoration-[1px]',
        'underline' => 'underline underline-offset-4 decoration-[1.5px]',
        default => 'underline underline-offset-4 decoration-[1.5px]',
    };
@endphp

<a
    href="{{ $href }}"
    @if ($wireNavigate) wire:navigate @endif
    {{ $attributes->class([$base, $variantClass]) }}
    style="color: var(--blue); {{ $attributes->get('style') }}"
    onmouseover="this.style.color='var(--blue-soft)'"
    onmouseout="this.style.color='var(--blue)'"
>
    {{ $slot }}
</a>
