@props([
    'name',
    'side' => 'right', // left | right
    'size' => 'md',    // sm | md | lg
    'focusable' => true,
    'closeOnBackdrop' => true,
    'closeOnEscape' => true,
])

@php
    $wireClose = $attributes->get('wire:close');
    $cardAttributes = $attributes->whereDoesntStartWith('wire:close');
@endphp

<div
    x-data="{
        name: @js($name),
        side: @js($side),
        get isOpen() { return this.$store.modals.is(this.name); },
        release: null,
        wasOpen: false,
        playEnter() {
            // Run the slide-in animation once per open. Driving the
            // class from Alpine instead of the server-rendered HTML
            // prevents Livewire morph from re-asserting it on every
            // roundtrip — which would otherwise replay the entrance
            // every time a wire:click inside the drawer hits the server.
            const enterClass = 'dashy-drawer-card--' + this.side;
            this.$nextTick(() => {
                if (this.$refs.card) {
                    this.$refs.card.classList.remove(enterClass);
                    void this.$refs.card.offsetWidth;
                    this.$refs.card.classList.add(enterClass);
                }
                if (this.$refs.backdrop) {
                    this.$refs.backdrop.classList.remove('dashy-fade-in');
                    void this.$refs.backdrop.offsetWidth;
                    this.$refs.backdrop.classList.add('dashy-fade-in');
                }
            });
        },
        sync() {
            if (this.isOpen && ! this.wasOpen) {
                this.wasOpen = true;
                this.playEnter();
                @if ($focusable)
                    this.$nextTick(() => {
                        this.release = window.dashyTrapFocus(this.$refs.card);
                    });
                @endif
            } else if (! this.isOpen && this.wasOpen) {
                this.wasOpen = false;
                if (this.release) { this.release(); this.release = null; }
                @if ($wireClose)
                    if (typeof $wire?.{{ $wireClose }} === 'function') {
                        $wire.{{ $wireClose }}();
                    }
                @endif
            }
        },
    }"
    x-effect="sync()"
    @keydown.escape.window="@if ($closeOnEscape) if (isOpen && $store.modals.top() === name) $store.modals.close(name) @endif"
>
    {{-- Always present in the live DOM (gated by x-show, not <template x-if>) so
         Livewire's morph traverses into the slot content when properties update.
         <template x-if> hides content in a DocumentFragment that morphing skips,
         which caused the detail drawer to show stale "no longer available" markup
         even after the task was hydrated. --}}
    <div x-show="$store.modals.is(@js($name))" x-cloak>
        <div
            x-ref="backdrop"
            class="dashy-modal-backdrop"
            @if ($closeOnBackdrop) @click="$store.modals.close(name)" @endif
        ></div>

        <div class="dashy-drawer-shell dashy-drawer-shell--{{ $side }}">
            <div
                x-ref="card"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                data-modal-name="{{ $name }}"
                {{ $cardAttributes->class([
                    'dashy-drawer-card',
                    'dashy-drawer--' . $size,
                ]) }}
            >
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
