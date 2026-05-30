<header
    class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-4 border-b border-[var(--border)] bg-[var(--bg)] px-3 py-4 sm:px-5 lg:px-7 lg:py-5"
    data-test="calendar-toolbar"
>
    {{-- Left cluster: CALENDAR tag + large display title + small inline Today/prev/next --}}
    <div class="flex min-w-0 items-center gap-3">
        <span
            class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em]"
            style="background-color: color-mix(in srgb, var(--state-success) 22%, var(--surface)); color: var(--state-success-strong);"
        >
            {{ __('Calendar') }}
        </span>
        <h1
            class="truncate font-display text-[24px] font-medium leading-tight text-[var(--ink)] sm:text-[28px]"
            data-test="calendar-title"
        >
            {{ $this->displayTitle }}
        </h1>

        <div class="ml-1 inline-flex items-center gap-0.5 text-[var(--ink-muted)]">
            <button
                type="button"
                wire:click="goToday"
                class="rounded-md px-2 py-1 text-[12px] font-medium transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                data-test="calendar-today"
            >
                {{ __('Today') }}
            </button>
            <button
                type="button"
                wire:click="prev"
                aria-label="{{ __('Previous') }}"
                class="inline-flex size-7 items-center justify-center rounded-md transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                data-test="calendar-prev"
            >
                <x-dashy.icon name="chevron-left" class="size-4" />
            </button>
            <button
                type="button"
                wire:click="next"
                aria-label="{{ __('Next') }}"
                class="inline-flex size-7 items-center justify-center rounded-md transition hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                data-test="calendar-next"
            >
                <x-dashy.icon name="chevron-right" class="size-4" />
            </button>
        </div>
    </div>

    {{-- Right cluster: soft segmented view switcher + outlined New event button --}}
    <div class="flex items-center gap-2">
        <div
            class="inline-flex rounded-full p-1"
            style="background-color: var(--surface-2);"
            data-test="calendar-view-switcher"
        >
            @foreach (['day' => __('Day'), 'week' => __('Week'), 'month' => __('Month')] as $value => $label)
                @php $isActive = $view === $value; @endphp
                <button
                    type="button"
                    wire:click="setView('{{ $value }}')"
                    class="inline-flex h-7 items-center rounded-full px-3 text-[13px] font-medium transition"
                    style="
                        background-color: {{ $isActive ? 'var(--cocoa)' : 'transparent' }};
                        color: {{ $isActive ? 'var(--surface)' : 'var(--ink-muted)' }};
                    "
                    @if (! $isActive)
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                    @endif
                    data-test="calendar-view-{{ $value }}"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <button
            type="button"
            wire:click="openCreate"
            class="dashy-btn dashy-btn--sm"
            style="background-color: transparent; color: var(--ink); border: 1px solid var(--border);"
            onmouseover="this.style.backgroundColor='var(--surface-2)';"
            onmouseout="this.style.backgroundColor='transparent';"
            data-test="calendar-add-event"
        >
            <x-dashy.icon name="plus" class="size-4" />
            <span>{{ __('New event') }}</span>
        </button>
    </div>
</header>
