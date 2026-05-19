@aware([
    'wireModel' => null,
    'defaultValue' => null,
])

@props([
    'value',
    'icon' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'button';
    // SSR initial state — picks up the current Livewire property value via the
    // parent's defaultValue so the right pill is highlighted before Alpine
    // boots and so non-JS feature tests stay assertable.
    $isInitiallySelected = $defaultValue !== null && (string) $defaultValue === (string) $value;
@endphp

<{{ $tag }}
    role="tab"
    @if ($href) href="{{ $href }}" @else type="button" @endif
    aria-selected="{{ $isInitiallySelected ? 'true' : 'false' }}"
    @if ($wireModel && ! $href)
        {{-- Single source of truth: $wire.<wireModel>. Updates the moment the
             Livewire property changes (click via $set, navigate, server action). --}}
        :aria-selected="$wire['{{ $wireModel }}'] === @js($value)"
        wire:click="$set('{{ $wireModel }}', '{{ $value }}')"
    @endif
    {{ $attributes->class(['dashy-tab']) }}
>
    @if ($icon)
        <x-dashy.icon :name="$icon" class="size-4" />
    @endif
    <span>{{ $slot }}</span>
</{{ $tag }}>
