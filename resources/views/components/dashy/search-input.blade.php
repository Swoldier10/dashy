@props([
    'placeholder' => null,
    'clearable' => true,
    'name' => null,
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
@endphp

<div
    class="dashy-search"
    @if ($clearable) x-data="{ get value() { const i = $el.querySelector('input'); return i?.value || ''; } }" @endif
>
    <span class="dashy-search-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.355 9.852l3.146 3.147a.75.75 0 1 0 1.06-1.06l-3.146-3.147A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
        </svg>
    </span>

    <input
        type="search"
        @if ($name) name="{{ $name }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        {{ $attributes }}
    />

    @if ($clearable)
        <button
            type="button"
            class="dashy-search-clear"
            x-show="value"
            x-cloak
            @click="$el.previousElementSibling.value = ''; $el.previousElementSibling.dispatchEvent(new Event('input', { bubbles: true }))"
            aria-label="{{ __('Clear') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
            </svg>
        </button>
    @endif
</div>
