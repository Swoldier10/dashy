@props([
    'as' => null, // null infers (a if href, otherwise button)
    'href' => null,
    'icon' => null,
    'iconVariant' => 'outline',
    'type' => 'button',
    'disabled' => false,
    'wireNavigate' => false,
])

@php
    $tag = $as ?? ($href ? 'a' : 'button');
@endphp

<{{ $tag }}
    role="menuitem"
    tabindex="-1"
    @if ($tag === 'a' && $href) href="{{ $href }}" @endif
    @if ($tag === 'button') type="{{ $type }}" @endif
    @if ($disabled) disabled aria-disabled="true" @endif
    @if ($wireNavigate) wire:navigate @endif
    {{ $attributes->class(['dashy-menu-item']) }}
>
    @if ($icon)
        <x-dashy.icon :name="$icon" :variant="$iconVariant" class="dashy-menu-item-icon size-4" />
    @endif
    <span class="min-w-0 flex-1">{{ $slot }}</span>
</{{ $tag }}>
