@props([
    'align' => 'end',       // start | center | end
    'position' => 'bottom', // bottom | top
    'closeOnClickInside' => true,
    'panelClass' => '',
])

{{-- <x-dashy.popover>
       <x-slot:trigger>… button markup …</x-slot:trigger>
       … popover content (typically a <x-dashy.menu>) …
     </x-dashy.popover>
     Owns open state + click-outside + escape. --}}

<div
    x-data="dashyPopover('{{ $align }}', '{{ $position }}')"
    @keydown.escape.window="open && close()"
    @scroll.window.passive="open && reposition()"
    @resize.window.passive="open && reposition()"
    {{ $attributes->class(['relative inline-block']) }}
>
    <div x-ref="trigger" @click="toggle()" class="contents">
        {{ $trigger ?? '' }}
    </div>

    {{-- wire:ignore.self preserves the panel's JS-set inline style (top/right/etc.
         from reposition()) across Livewire morphs. Without it, the server HTML
         has no style attribute, so morph strips the inline coords and the
         position:fixed panel falls back to its DOM-static spot — off-screen
         when the popover stays open after a server action (e.g. logManual
         saving a time entry in the time-tracking popover). Children still
         morph normally so list contents refresh. --}}
    <div
        x-ref="panel"
        wire:ignore.self
        x-show="open"
        x-cloak
        x-transition.opacity.duration.120ms
        @click.outside="close()"
        @if ($closeOnClickInside) @click="setTimeout(() => close(), 0)" @endif
        @class([
            'dashy-popover-panel',
            'dashy-pop-in',
            'dashy-popover-panel--align-' . $align,
            'dashy-popover-panel--position-' . $position,
            $panelClass,
        ])
    >
        {{ $slot }}
    </div>
</div>
