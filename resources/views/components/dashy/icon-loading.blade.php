@php
    $hasSize = str_contains((string) $attributes->get('class'), 'size-');
@endphp

<svg
    {{ $attributes->class([
        'animate-spin' => true,
        'size-5' => ! $hasSize,
        'shrink-0' => true,
    ]) }}
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    aria-hidden="true"
>
    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"></circle>
    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
</svg>
