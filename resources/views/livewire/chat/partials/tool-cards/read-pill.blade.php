@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     *
     * Compact ghost-pill rendering for auto_read tool calls. The actual data
     * went to the LLM via function_call_output — this view just tells the
     * user what was looked up, so the agentic loop stays transparent.
     */
    $status = $card['status'] ?? 'executed';
    $label = (string) ($card['label'] ?? __('Lookup'));
    $icon = (string) ($card['icon'] ?? 'magnifying-glass');
    $count = $card['count'] ?? null;
    $isFailed = $status === 'failed';
    $isExecuted = $status === 'executed';
    $color = $isFailed ? 'var(--state-error)' : 'var(--ink-muted)';
@endphp

<div
    class="mt-2 inline-flex max-w-full items-center gap-2 rounded-full border px-3 py-1.5 text-xs sm:text-[13px]"
    style="border-color: var(--border-mid); background-color: var(--surface-2); color: {{ $color }};"
    data-test="tool-call-card"
    data-tool="{{ $card['name'] ?? '' }}"
    data-mode="auto_read"
    data-status="{{ $status }}"
>
    <x-dashy.icon :name="$isFailed ? 'exclamation-triangle' : $icon" class="size-3.5 shrink-0" />
    <span class="truncate">{{ $label }}</span>
    @if (! $isFailed && $isExecuted)
        <span aria-hidden="true" style="color: var(--state-success);">✓</span>
    @endif
</div>
