@props([
    'href',
    'active' => false,
    'dataTest' => null,
    'wireKey' => null,
])

{{-- Active-state workspace sidebar link: cream-on-surface when active, muted
     with hover lift otherwise. The leading icon/shape, label, and trailing
     count are passed as the slot so every sidebar entry shares one chrome. --}}
<a
    href="{{ $href }}"
    wire:navigate
    @if ($wireKey) wire:key="{{ $wireKey }}" @endif
    @if ($active) aria-current="page" @endif
    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
    style="
        background-color: {{ $active ? 'var(--surface)' : 'transparent' }};
        color: {{ $active ? 'var(--ink)' : 'var(--ink-muted)' }};
        box-shadow: {{ $active ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none' }};
    "
    @if (! $active)
        onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
    @endif
    @if ($dataTest) data-test="{{ $dataTest }}" @endif
>{{ $slot }}</a>
