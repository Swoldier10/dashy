@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     *
     * Generic single-target write card. Renders a one-line "what's about to
     * happen" summary plus Apply / Discard. Used by every confirm_write tool
     * whose proposal does not need user editing of fields (move, assign,
     * timer, priority, etc.). When the proposal DOES need a form (create_task,
     * create_project), the dedicated partial is used instead.
     */
    $status = $card['status'] ?? 'pending';
    $title = (string) ($card['title'] ?? __('Action'));
    $summary = (string) ($card['summary'] ?? '');
    $icon = (string) ($card['icon'] ?? 'pencil-square');
    $destructive = (bool) ($card['destructive'] ?? false);
    $errors = (array) ($card['validation_errors'] ?? []);
    $applyColor = $destructive ? 'var(--state-error)' : 'var(--blue)';
    $applyLabel = $destructive ? __('Delete') : __('Apply');
@endphp

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="{{ $card['name'] ?? '' }}"
    data-mode="compact_write"
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
            <p class="text-[13px] font-semibold uppercase tracking-wide" style="color: var(--ink-muted);">
                {{ $title }}
            </p>
            @if ($summary !== '')
                <p class="mt-0.5 truncate text-sm" style="color: var(--ink);">{{ $summary }}</p>
            @endif

            @if ($errors !== [])
                <ul class="mt-2 list-disc space-y-0.5 pl-5 text-xs" style="color: var(--state-error);">
                    @foreach ($errors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex shrink-0 items-center gap-2">
            @if ($status === 'pending')
                <button
                    type="button"
                    wire:click="discardToolCall({{ $message->id }})"
                    wire:loading.attr="disabled"
                    class="inline-flex min-h-9 items-center justify-center rounded-full border px-3 py-1.5 text-xs font-medium"
                    style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                    data-test="compact-discard"
                >
                    {{ __('Discard') }}
                </button>
                <button
                    type="button"
                    wire:click="confirmToolCall({{ $message->id }})"
                    wire:loading.attr="disabled"
                    class="inline-flex min-h-9 items-center justify-center rounded-full px-3 py-1.5 text-xs font-medium"
                    style="background-color: {{ $applyColor }}; color: var(--surface);"
                    data-test="compact-apply"
                >
                    {{ $applyLabel }}
                </button>
            @elseif ($status === 'created')
                <span
                    class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium"
                    style="color: var(--state-success);"
                    data-test="compact-applied"
                >
                    <span aria-hidden="true">✓</span> {{ __('Applied') }}
                </span>
            @elseif ($status === 'discarded')
                <span class="text-xs italic" style="color: var(--ink-dim);" data-test="compact-discarded">
                    {{ __('Discarded') }}
                </span>
            @elseif ($status === 'failed')
                <span class="text-xs italic" style="color: var(--state-error);" data-test="compact-failed">
                    {{ __('Could not apply') }}
                </span>
            @endif
        </div>
    </div>
</div>
