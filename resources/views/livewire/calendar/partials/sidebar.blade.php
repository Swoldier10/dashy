<div class="dashy-sidebar flex h-full flex-col gap-5 overflow-y-auto p-5" data-test="calendar-sidebar">
    {{-- Mini month calendar --}}
    <section data-test="calendar-mini">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-[13px] font-medium text-[var(--ink)]">
                {{ $this->miniMonthLabel }}
            </span>
            <div class="inline-flex items-center gap-0.5 text-[var(--ink-muted)]">
                <button
                    type="button"
                    wire:click="prev"
                    aria-label="{{ __('Previous') }}"
                    class="inline-flex size-6 items-center justify-center rounded-md transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                >
                    <x-dashy.icon name="chevron-left" class="size-3.5" />
                </button>
                <button
                    type="button"
                    wire:click="next"
                    aria-label="{{ __('Next') }}"
                    class="inline-flex size-6 items-center justify-center rounded-md transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                >
                    <x-dashy.icon name="chevron-right" class="size-3.5" />
                </button>
            </div>
        </div>

        <div class="dashy-mini-cal__grid mb-1">
            @foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $dow)
                <div class="text-center text-[10px] font-semibold uppercase tracking-wider text-[var(--ink-dim)]">
                    {{ $dow }}
                </div>
            @endforeach
        </div>

        <div class="dashy-mini-cal__grid">
            @foreach ($this->miniMonthWeeks as $week)
                @foreach ($week as $cell)
                    <button
                        type="button"
                        wire:click="setAnchor('{{ $cell['date']->toDateString() }}')"
                        @class([
                            'dashy-mini-cal__day',
                            'dashy-mini-cal__day--out' => ! $cell['inMonth'],
                            'dashy-mini-cal__day--today' => $cell['isToday'],
                            'dashy-mini-cal__day--anchor' => $cell['isAnchor'] && ! $cell['isToday'],
                        ])
                    >
                        {{ $cell['date']->day }}
                    </button>
                @endforeach
            @endforeach
        </div>
    </section>
</div>
