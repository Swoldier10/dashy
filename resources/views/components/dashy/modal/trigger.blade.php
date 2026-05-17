@props([
    'name',
])

<span
    x-data
    @click="$store.modals.open(@js($name))"
    {{ $attributes }}
>
    {{ $slot }}
</span>
