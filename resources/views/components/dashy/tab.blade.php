@aware([
    'name' => null,
    'wireModel' => null,
])

@props([
    'value',
    'icon' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    role="tab"
    @if ($href) href="{{ $href }}" @else type="button" @endif
    @if ($wireModel) wire:click="$set('{{ $wireModel }}', '{{ $value }}')" @endif
    @if (! $href) :aria-selected="value === @js($value)" @click="value = @js($value)" @endif
    @if ($href) aria-selected="false" @endif
    {{ $attributes->class(['dashy-tab']) }}
>
    @if ($icon)
        <x-dashy.icon :name="$icon" class="size-4" />
    @endif
    <span>{{ $slot }}</span>
</{{ $tag }}>
