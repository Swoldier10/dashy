@aware([
    'name' => null,
    'variant' => 'stacked',
    'wireModel' => null,
])

@props([
    'value',
    'icon' => null,
    'label' => null,
])

@php
    $variantClass = 'dashy-radio--' . $variant;
@endphp

<label {{ $attributes->only('class')->class([$variantClass]) }}>
    <input
        type="radio"
        @if ($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        @if ($wireModel) wire:model="{{ $wireModel }}" @endif
        {{ $attributes->except('class') }}
    />
    @if ($variant === 'segmented')
        <span class="dashy-radio-pill">
            @if ($icon)
                <x-dashy.icon :name="$icon" class="size-4" />
            @endif
            <span>{{ $label ?? $slot }}</span>
        </span>
    @else
        <span class="text-sm" style="color: var(--ink);">{{ $label ?? $slot }}</span>
    @endif
</label>
