@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     *
     * Consolidated bulk-write card. Shows the title (e.g. "Move 4 tasks"),
     * an optional subtitle (target status / assignee), the full list of
     * tasks being acted on, and Apply / Discard buttons. Destructive bulk
     * tools (bulk_delete_tasks) render with red emphasis.
     */
    $status = $card['status'] ?? 'pending';
    $title = (string) ($card['title'] ?? __('Apply'));
    $subtitle = (string) ($card['subtitle'] ?? '');
    $icon = (string) ($card['icon'] ?? 'queue-list');
    $destructive = (bool) ($card['destructive'] ?? false);
    $rows = (array) ($card['rows'] ?? []);
    $errors = (array) ($card['validation_errors'] ?? []);
    $applyColor = $destructive ? 'var(--state-error)' : 'var(--blue)';
    $applyLabel = $destructive ? __('Delete all') : __('Apply all');
@endphp

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="{{ $card['name'] ?? '' }}"
    data-mode="bulk_write"
    data-status="{{ $status }}"
>
    <div class="flex items-start gap-3 px-4 py-3">
        <div
            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full"
            style="background-color: var(--surface-3); color: {{ $applyColor }};"
        >
            <x-dashy.icon :name="$icon" class="size-4" />
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold" style="color: var(--ink);">{{ $title }}</p>
            @if ($subtitle !== '')
                <p class="mt-0.5 text-xs" style="color: var(--ink-muted);">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if ($rows !== [])
        <ul
            class="max-h-56 overflow-y-auto border-t px-4 py-2"
            style="border-color: var(--border-mid);"
            data-test="bulk-row-list"
        >
            @foreach ($rows as $row)
                <li class="flex items-center gap-2 py-1 text-sm" style="color: var(--ink);">
                    <span aria-hidden="true" style="color: var(--ink-dim);">•</span>
                    <span class="truncate">{{ $row['label'] ?? '#'.($row['task_id'] ?? '?') }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($errors !== [])
        <ul class="border-t px-4 py-2 text-xs" style="border-color: var(--border-mid); color: var(--state-error);">
            @foreach ($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <div
        class="flex items-center justify-end gap-2 border-t px-4 py-3"
        style="border-color: var(--border-mid);"
    >
        @if ($status === 'pending')
            <button
                type="button"
                wire:click="discardToolCall({{ $message->id }})"
                wire:loading.attr="disabled"
                class="inline-flex min-h-9 items-center justify-center rounded-full border px-4 py-1.5 text-sm font-medium"
                style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                data-test="bulk-discard"
            >
                {{ __('Discard') }}
            </button>
            <button
                type="button"
                wire:click="confirmToolCall({{ $message->id }})"
                wire:loading.attr="disabled"
                class="inline-flex min-h-9 items-center justify-center rounded-full px-4 py-1.5 text-sm font-medium"
                style="background-color: {{ $applyColor }}; color: var(--surface);"
                data-test="bulk-apply"
            >
                {{ $applyLabel }}
            </button>
        @elseif ($status === 'created')
            <span
                class="inline-flex items-center gap-1 text-sm font-medium"
                style="color: var(--state-success);"
                data-test="bulk-applied"
            >
                <span aria-hidden="true">✓</span> {{ __('Applied') }}
            </span>
        @elseif ($status === 'discarded')
            <span class="text-xs italic" style="color: var(--ink-dim);" data-test="bulk-discarded">
                {{ __('Discarded') }}
            </span>
        @elseif ($status === 'failed')
            <span class="text-xs italic" style="color: var(--state-error);" data-test="bulk-failed">
                {{ __('Could not apply') }}
            </span>
        @endif
    </div>
</div>
