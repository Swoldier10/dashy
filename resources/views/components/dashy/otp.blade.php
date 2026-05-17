@props([
    'length' => 6,
    'name' => null,
    'label' => null,
    'labelSrOnly' => false,
])

@php
    $id = 'dashy-otp-' . ($name ?? 'code');

    // Pull through x-model / wire:model so the parent's reactive state
    // stays bound to the joined digit value.
    $xModel = $attributes->get('x-model');
    $wireModel = $attributes->get('wire:model');
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}-0" @class(['dashy-label', 'sr-only' => $labelSrOnly])>{{ $label }}</label>
    @endif

    <div
        class="dashy-otp"
        x-data="{
            digits: Array.from({ length: {{ (int) $length }} }, () => ''),
            value() { return this.digits.join(''); },
            sync(idx, ev) {
                const v = (ev.target.value || '').replace(/\D/g, '').slice(-1);
                this.digits[idx] = v;
                ev.target.value = v;
                if (v && idx < {{ (int) $length - 1 }}) {
                    this.$refs['cell' + (idx + 1)]?.focus();
                }
                this.$dispatch('input', this.value());
            },
            handleKey(idx, ev) {
                if (ev.key === 'Backspace' && ! this.digits[idx] && idx > 0) {
                    this.$refs['cell' + (idx - 1)]?.focus();
                } else if (ev.key === 'ArrowLeft' && idx > 0) {
                    this.$refs['cell' + (idx - 1)]?.focus();
                } else if (ev.key === 'ArrowRight' && idx < {{ (int) $length - 1 }}) {
                    this.$refs['cell' + (idx + 1)]?.focus();
                }
            },
            handlePaste(ev) {
                const text = (ev.clipboardData || window.clipboardData).getData('text') || '';
                const digits = text.replace(/\D/g, '').slice(0, {{ (int) $length }});
                if (! digits) return;
                ev.preventDefault();
                for (let i = 0; i < {{ (int) $length }}; i++) {
                    this.digits[i] = digits[i] ?? '';
                }
                this.$nextTick(() => {
                    const last = Math.min(digits.length, {{ (int) $length }}) - 1;
                    this.$refs['cell' + Math.max(last, 0)]?.focus();
                    this.$dispatch('input', this.value());
                });
            },
        }"
        @if ($xModel)
            x-init="$watch('digits', () => { {{ $xModel }} = value(); })"
            x-effect="
                if (typeof {{ $xModel }} === 'string' && {{ $xModel }} !== value()) {
                    const v = ({{ $xModel }} || '').replace(/\D/g, '').slice(0, {{ (int) $length }});
                    digits = Array.from({ length: {{ (int) $length }} }, (_, i) => v[i] ?? '');
                }
            "
        @endif
        @if ($name)
            x-init="if ($refs.hidden) $refs.hidden.value = value()"
            x-effect="if ($refs.hidden) $refs.hidden.value = value()"
        @endif
    >
        @for ($i = 0; $i < (int) $length; $i++)
            <input
                id="{{ $id }}-{{ $i }}"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="1"
                x-model="digits[{{ $i }}]"
                x-ref="cell{{ $i }}"
                @input="sync({{ $i }}, $event)"
                @keydown="handleKey({{ $i }}, $event)"
                @paste="handlePaste($event)"
                aria-label="{{ __('Digit :n', ['n' => $i + 1]) }}"
            />
        @endfor

        @if ($name)
            <input type="hidden" name="{{ $name }}" x-ref="hidden" />
        @endif
    </div>
</div>
