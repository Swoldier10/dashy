@props([
    'name',
    'size' => 'md', // sm | md | lg | xl | 2xl
    'focusable' => true,
    'closeOnBackdrop' => true,
    'closeOnEscape' => true,
])

@php
    // The Livewire-side close method is passed via wire:close="…".
    $wireClose = $attributes->get('wire:close');
    // Strip wire:close so it doesn't get reflected as an HTML attribute.
    $cardAttributes = $attributes->whereDoesntStartWith('wire:close');
@endphp

<div
    x-data="{
        name: @js($name),
        get isOpen() { return this.$store.modals.is(this.name); },
        release: null,
        wasOpen: false,
        playEnter() {
            // Run the open animation once per modal-open by toggling the
            // class on the next frame. Driving the animation from Alpine
            // (instead of leaving the class in the server-rendered HTML)
            // keeps Livewire morph from re-asserting it on every roundtrip
            // — which would otherwise replay the pop-in/fade-in every time
            // a wire:click inside the modal hits the server.
            this.$nextTick(() => {
                if (this.$refs.card) {
                    this.$refs.card.classList.remove('dashy-pop-in');
                    void this.$refs.card.offsetWidth;
                    this.$refs.card.classList.add('dashy-pop-in');
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
         which caused dynamic content (e.g. project-settings statuses) to render
         stale or empty after the modal was opened. --}}
    <div x-show="$store.modals.is(@js($name))" x-cloak>
        <div
            x-ref="backdrop"
            class="dashy-modal-backdrop"
            @if ($closeOnBackdrop) @click="$store.modals.close(name)" @endif
        ></div>

        <div class="dashy-modal-shell">
            <div
                x-ref="card"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                data-modal-name="{{ $name }}"
                {{ $cardAttributes->class([
                    'dashy-modal-card',
                    'dashy-modal--' . $size,
                ]) }}
            >
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
