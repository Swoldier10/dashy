@php
    $rowId   = $mode === 'create' ? $item['cid']  : (string) $item->id;
    $rowName = $mode === 'create' ? $item['name'] : $item->name;
    $color   = "var({$category->colorVar()})";
    $renameMethod = $mode === 'create' ? 'renameBufferedStatus' : 'renameStatus';
    $deleteMethod = $mode === 'create' ? 'deleteBufferedStatus' : 'deleteStatus';
@endphp

<div
    wire:key="status-{{ $mode }}-{{ $rowId }}"
    data-id="{{ $rowId }}"
    class="group relative flex items-center gap-2 rounded-md px-2 py-1.5"
    style="background-color: var(--surface-2);"
>
    <span class="dashy-status-handle inline-flex shrink-0 cursor-grab p-1.5" aria-hidden="true" style="color: var(--ink-dim);">
        <x-dashy.icon name="bars-3" class="size-4" />
    </span>
    <span class="size-3 shrink-0 rounded-full" style="background-color: {{ $color }};"></span>

    <input
        type="text"
        x-data="{ value: @js($rowName), original: @js($rowName) }"
        x-model="value"
        x-on:blur="
            const trimmed = value.trim();
            if (trimmed !== '' && trimmed !== original) {
                $wire.{{ $renameMethod }}(@js($rowId), trimmed);
                original = trimmed;
            } else if (trimmed === '') {
                value = original;
            }
        "
        x-on:keydown.enter.prevent="$event.target.blur()"
        class="flex-1 bg-transparent text-sm outline-none {{ $canManage ? 'pr-7' : '' }}"
        style="color: var(--ink);"
        maxlength="60"
        @disabled(! $canManage)
        data-test="status-name-{{ $mode }}-{{ $rowId }}"
    />

    @if ($canManage)
        <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-0.5 opacity-0 transition group-hover:opacity-100 focus:opacity-100"
            style="color: var(--ink-dim);"
            wire:click="{{ $deleteMethod }}(@js($rowId))"
            onmouseover="this.style.color='var(--state-error)';"
            onmouseout="this.style.color='var(--ink-dim)';"
            aria-label="{{ __('Delete status') }}"
            data-test="status-delete-{{ $mode }}-{{ $rowId }}"
        >
            <x-dashy.icon name="trash" class="size-3.5" />
        </button>
    @endif
</div>
