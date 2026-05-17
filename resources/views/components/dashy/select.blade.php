@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'errorKey' => null,
    'showError' => true,
])

@php
    if (! $name) {
        foreach ($attributes->getAttributes() as $key => $value) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($value) ? $value : null;
                break;
            }
        }
    }

    $errorBagKey = $errorKey ?? $name;
    $id = $attributes->get('id') ?? ($name ? 'dashy-select-' . str_replace(['.', '['], ['-', '-'], (string) $name) : null);
@endphp

<div class="grid gap-1.5">
    @if ($label)
        <x-dashy.label :for="$id">{{ $label }}</x-dashy.label>
    @endif

    @if ($description)
        <p class="dashy-help" style="margin-top:-2px;">{{ $description }}</p>
    @endif

    <div class="dashy-select-wrap">
        <select
            @if ($id) id="{{ $id }}" @endif
            @if ($name) name="{{ $name }}" @endif
            {{ $attributes->merge(['class' => 'dashy-select']) }}
        >
            @if ($placeholder)
                <option value="" disabled selected>{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        <span class="dashy-select-chevron">
            <x-dashy.icon name="chevron-down" class="size-4" />
        </span>
    </div>

    @if ($showError && $errorBagKey)
        <x-dashy.field-error :name="$errorBagKey" />
    @endif
</div>
