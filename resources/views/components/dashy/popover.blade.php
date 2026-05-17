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
    x-data="{
        open: false,
        toggle() { this.open = ! this.open; },
        close() { this.open = false; },
    }"
    @keydown.escape.window="open && close()"
    {{ $attributes->class(['relative inline-block']) }}
>
    <div @click="toggle()" class="contents">
        {{ $trigger ?? '' }}
    </div>

    <div
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
