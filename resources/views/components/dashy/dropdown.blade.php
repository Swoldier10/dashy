{{-- Alias of <x-dashy.popover>. Lets the existing call sites that came
     from <flux:dropdown> read naturally as "dropdown" while sharing one
     implementation. Same API: <x-slot:trigger>…</x-slot:trigger>, default
     slot is the panel content. --}}
@props([
    'align' => 'end',
    'position' => 'bottom',
    'closeOnClickInside' => true,
    'panelClass' => '',
])

<x-dashy.popover
    :align="$align"
    :position="$position"
    :closeOnClickInside="$closeOnClickInside"
    :panelClass="$panelClass"
    {{ $attributes }}
>
    @isset($trigger)
        <x-slot:trigger>{{ $trigger }}</x-slot:trigger>
    @endisset
    {{ $slot }}
</x-dashy.popover>
