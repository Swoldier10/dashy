<div
    {{ $attributes->class([
        'dashy-radio-group',
        'dashy-radio-group--' . $variant,
    ]) }}
    role="radiogroup"
>
    {{ $slot }}
</div>
