@php
    $pill = $this->dateTimePill;
    $summary = $this->tomorrowSummary;
@endphp
<div class="flex flex-1 flex-col overflow-y-auto">
    <div class="m-auto flex w-full max-w-3xl flex-col items-center justify-center gap-5 px-4 py-10 sm:gap-7 sm:px-6">
        {{-- Date/time pill --}}
        <div
            class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-wider"
            style="background-color: var(--surface); border-color: var(--border); color: var(--ink-muted);"
            data-test="chat-date-pill"
        >
            <span class="dashy-pulse" aria-hidden="true"></span>
            <span>{{ $pill['date'] }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ $pill['time'] }}</span>
        </div>

        {{-- Greeting --}}
        <h1
            class="text-center font-display text-3xl font-normal leading-tight sm:text-4xl md:text-5xl"
            style="color: var(--ink); letter-spacing: -0.01em;"
            data-test="chat-greeting"
        >
            {{ $this->greeting }}.
        </h1>

        {{-- Subtitle with tomorrow counts --}}
        <p class="max-w-xl text-center text-sm leading-relaxed sm:text-base" style="color: var(--ink-muted);" data-test="chat-subtitle">
            {{ __('You have') }}
            <strong style="color: var(--ink);">{{ trans_choice('{0} no meetings|{1} :count meeting|[2,*] :count meetings', $summary['meetings'], ['count' => $summary['meetings']]) }}</strong>
            {{ __('and') }}
            <strong style="color: var(--ink);">{{ trans_choice('{0} no tasks|{1} :count task|[2,*] :count tasks', $summary['tasks'], ['count' => $summary['tasks']]) }}</strong>
            {{ __('on deck for tomorrow. Want me to help you prep?') }}
        </p>

        {{-- Composer --}}
        <div class="w-full">
            @include('livewire.chat.partials.composer', ['large' => true])
        </div>
    </div>
</div>
