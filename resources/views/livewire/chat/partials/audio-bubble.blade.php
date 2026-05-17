{{--
    Custom audio bubble used in both the composer thumbnail row and the
    thread render. The hidden <audio x-ref="audio"> is the actual playback
    surface; we drive it via $refs (which returns the raw DOM element)
    rather than wrapping it in Alpine's reactive proxy — HTMLMediaElement
    methods enforce strict `this` binding and would otherwise throw.

    Props:
      $url      string       Public URL of the audio file.
      $duration float|null   Recorded length in seconds (server stores it
                             from the upload filename — needed because
                             MediaRecorder WebMs report duration = Infinity).
      $compact  bool         Smaller variant used inside the composer.
--}}
@props([
    'url' => '',
    'duration' => null,
    'compact' => false,
])

@php
    $compact = (bool) $compact;
    $duration = is_numeric($duration) ? (float) $duration : 0;
@endphp

<div
    x-data="{
        knownDuration: {{ $duration }},
        currentTime: 0,
        playing: false,
        format(seconds) {
            const total = Math.max(0, Math.floor(Number.isFinite(seconds) ? seconds : 0));
            const m = Math.floor(total / 60);
            const s = (total % 60).toString().padStart(2, '0');
            return m + ':' + s;
        },
        get duration() {
            const fromAudio = this.$refs.audio?.duration;
            if (Number.isFinite(fromAudio) && fromAudio > 0) return fromAudio;
            return this.knownDuration;
        },
        progressPercent() {
            const d = this.duration;
            return d > 0 ? Math.min(100, (this.currentTime / d) * 100) : 0;
        },
        toggle() {
            const audio = this.$refs.audio;
            if (!audio) return;
            if (audio.paused) {
                audio.play().catch(err => console.error('Audio playback failed', err));
            } else {
                audio.pause();
            }
        },
        seek(event) {
            const audio = this.$refs.audio;
            if (!audio || this.duration <= 0) return;
            const bar = event.currentTarget;
            const rect = bar.getBoundingClientRect();
            const ratio = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
            audio.currentTime = ratio * this.duration;
        },
    }"
    @class([
        'flex items-center gap-2 rounded-full',
        'px-2 py-1.5' => $compact,
        'px-3 py-2' => ! $compact,
    ])
    style="background-color: var(--surface-2);"
>
    {{-- Hidden audio element — drives playback. We listen to its events to
         keep the UI in sync without wrapping the element in Alpine's proxy. --}}
    <audio
        x-ref="audio"
        src="{{ $url }}"
        preload="metadata"
        x-on:loadedmetadata="window.fixWebmDuration?.($refs.audio)"
        x-on:play="playing = true"
        x-on:pause="playing = false"
        x-on:ended="playing = false; currentTime = 0"
        x-on:timeupdate="currentTime = $refs.audio.currentTime"
        class="hidden"
    ></audio>

    <button
        type="button"
        x-on:click="toggle()"
        @class([
            'flex shrink-0 items-center justify-center rounded-full transition',
            'size-7' => $compact,
            'size-8' => ! $compact,
        ])
        style="background-color: var(--accent); color: var(--bg-deep);"
        x-bind:aria-label="playing ? '{{ __('Pause') }}' : '{{ __('Play') }}'"
        data-test="audio-bubble-toggle"
    >
        <x-dashy.icon name="play" class="size-3.5" x-show="!playing" />
        <x-dashy.icon name="pause" class="size-3.5" x-show="playing" x-cloak />
    </button>

    <button
        type="button"
        x-on:click="seek($event)"
        @class([
            'relative h-1.5 flex-1 overflow-hidden rounded-full',
            'min-w-[80px]' => $compact,
            'min-w-[140px]' => ! $compact,
        ])
        style="background-color: var(--border-mid);"
        aria-label="{{ __('Seek') }}"
    >
        <span
            class="absolute left-0 top-0 h-full rounded-full transition-[width]"
            style="background-color: var(--accent);"
            x-bind:style="`width: ${progressPercent()}%`"
        ></span>
    </button>

    <span
        class="shrink-0 text-xs tabular-nums"
        style="color: var(--ink-muted);"
        x-text="format(currentTime) + ' / ' + format(duration)"
    ></span>
</div>
