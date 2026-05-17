@props([
    'size' => 'md', // sm | md | lg | xl
    'as' => 'h2',
    'display' => null, // bool — force display font (Fraunces); auto for lg/xl
])

@php
    [$sizeClass, $defaultDisplay] = match ($size) {
        'sm' => ['text-base font-semibold', false],
        'md' => ['text-lg font-semibold', false],
        'lg' => ['text-xl', true],
        'xl' => ['text-2xl sm:text-3xl', true],
        default => ['text-lg font-semibold', false],
    };

    $useDisplay = $display ?? $defaultDisplay;
@endphp

<{{ $as }}
    {{ $attributes->class([
        $sizeClass,
        'font-display' => $useDisplay,
        'tracking-tight',
    ]) }}
    style="color: var(--ink); {{ $attributes->get('style') }}"
>
    {{ $slot }}
</{{ $as }}>
