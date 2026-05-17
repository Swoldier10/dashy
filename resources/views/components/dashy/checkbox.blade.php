@props([
    'name' => null,
    'value' => 1,
    'label' => null,
])

@php
    if (! $name) {
        foreach ($attributes->getAttributes() as $key => $val) {
            if (str_starts_with($key, 'wire:model')) {
                $name = is_string($val) ? $val : null;
                break;
            }
        }
    }
@endphp

<label class="inline-flex items-center gap-2 cursor-pointer">
    <span class="dashy-checkbox">
        <input
            type="checkbox"
            @if ($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            {{ $attributes }}
        />
        <svg class="dashy-check size-3" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: var(--color-brand-cocoa);">
            <path d="M2.5 6.2 5 8.7l4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
    @if ($label || $slot->isNotEmpty())
        <span class="text-sm" style="color: var(--ink);">{{ $label ?? $slot }}</span>
    @endif
</label>
