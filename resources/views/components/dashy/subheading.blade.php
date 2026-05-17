@props([
    'as' => 'p',
])

<{{ $as }}
    {{ $attributes->class(['text-sm leading-relaxed']) }}
    style="color: var(--ink-muted); {{ $attributes->get('style') }}"
>
    {{ $slot }}
</{{ $as }}>
