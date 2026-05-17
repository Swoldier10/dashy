@props([
    'padding' => 'md',  // sm | md | lg
    'as' => 'div',
])

<{{ $as }}
    {{ $attributes->class([
        'dashy-card',
        'dashy-card--' . $padding,
    ]) }}
>
    {{ $slot }}
</{{ $as }}>
