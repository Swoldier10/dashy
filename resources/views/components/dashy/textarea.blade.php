@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'rows' => 3,
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
    $id = $attributes->get('id') ?? ($name ? 'dashy-textarea-' . str_replace(['.', '['], ['-', '-'], (string) $name) : null);
@endphp

<div class="grid gap-1.5">
    @if ($label)
        <x-dashy.label :for="$id">{{ $label }}</x-dashy.label>
    @endif

    @if ($description)
        <p class="dashy-help" style="margin-top:-2px;">{{ $description }}</p>
    @endif

    <textarea
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'dashy-input']) }}
    >{{ $slot }}</textarea>

    @if ($showError && $errorBagKey)
        <x-dashy.field-error :name="$errorBagKey" />
    @endif
</div>
