@props(['defaultValue' => null])

<div
    x-data="{ value: @js($defaultValue ?? '') }"
    role="tablist"
    {{ $attributes->class(['dashy-tabs']) }}
>
    {{ $slot }}
</div>
