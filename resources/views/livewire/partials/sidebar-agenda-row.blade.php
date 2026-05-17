@php
    /** @var \App\Domains\Calendar\DTOs\AgendaRow $row */
    $tag = $row->href !== null ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($row->href !== null)
        href="{{ $row->href }}"
        wire:navigate
    @endif
    class="flex items-start gap-3 rounded-lg px-1 py-1.5 transition"
    @if ($row->href !== null)
        onmouseover="this.style.backgroundColor='var(--surface-2)'"
        onmouseout="this.style.backgroundColor='transparent'"
    @endif
    style="background-color: transparent;"
>
    <span
        class="mt-1 block w-[3px] shrink-0 self-stretch rounded-full"
        style="background-color: var({{ $row->accent }});"
        aria-hidden="true"
    ></span>
    <span class="min-w-0 flex-1">
        <span class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wider" style="color: var(--ink-dim);">
            <span>{{ $row->timeLabel }}</span>
            @if ($row->timeLabel !== '' && $row->kindLabel !== '')
                <span aria-hidden="true">—</span>
            @endif
            <span>{{ $row->kindLabel }}</span>
        </span>
        <span class="block truncate text-sm" style="color: var(--ink);">{{ $row->title }}</span>
    </span>
</{{ $tag }}>
