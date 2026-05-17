@props([
    'name' => null,
    'initials' => null,
    'src' => null,
    'size' => 'md', // xs | sm | md | lg
    'online' => false,
])

@php
    if (! $initials && $name) {
        $parts = preg_split('/\s+/', trim((string) $name));
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : '';
        $initials = mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
    }
@endphp

<span
    {{ $attributes->class([
        'dashy-avatar',
        'dashy-avatar--' . $size,
        'relative',
    ]) }}
    role="img"
    aria-label="{{ $name }}"
>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" />
    @elseif ($initials)
        <span aria-hidden="true">{{ $initials }}</span>
    @endif

    @if ($online)
        <span
            aria-hidden="true"
            class="absolute -right-0.5 -bottom-0.5 size-2.5 rounded-full"
            style="background-color: var(--state-success); box-shadow: 0 0 0 2px var(--bg-deep);"
        ></span>
    @endif
</span>
