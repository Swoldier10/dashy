@props([
    'defaultValue' => null,
    'wireModel' => null,
])

{{-- Both props are exposed to the <x-dashy.tab> children via the @ aware
     directive on the child — defaultValue drives the SSR aria-selected,
     wireModel drives wire:click and the :aria-selected binding.

     No x-data here on purpose: the selected tab is owned solely by Livewire
     ($wire.<wireModel>). Mirroring it locally in Alpine caused the pill to
     drift "a step behind" after wire:navigate, because Alpine's x-data is
     evaluated once at init and never re-read when morph swaps in a new
     default-value. --}}
<div
    role="tablist"
    {{ $attributes->class(['dashy-tabs']) }}
>
    {{ $slot }}
</div>
