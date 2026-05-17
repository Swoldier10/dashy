{{-- Credit: Lucide (https://lucide.dev) --}}

@php
    $hasSize = str_contains((string) $attributes->get('class'), 'size-');
@endphp

<svg
    {{ $attributes->class([
        'shrink-0' => true,
        'size-5' => ! $hasSize,
    ]) }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    <path d="m7 15 5 5 5-5" />
    <path d="m7 9 5-5 5 5" />
</svg>
