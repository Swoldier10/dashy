@props([
    'for' => null,
    'required' => false,
])

<label
    @if ($for) for="{{ $for }}" @endif
    {{ $attributes->class(['dashy-label']) }}
>
    {{ $slot }}
    @if ($required)
        <span aria-hidden="true" style="color: var(--state-error);">*</span>
    @endif
</label>
