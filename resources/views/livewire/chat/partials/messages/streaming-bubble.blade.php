<div
    class="text-[15px] leading-relaxed"
    style="color: var(--ink);"
    wire:key="streaming-{{ $activeChatId }}"
>
    @if ($streamingAssistant !== '')
        <div class="whitespace-pre-wrap break-words" wire:stream="streamingAssistant">{{ $streamingAssistant }}</div>
    @else
        <div class="flex items-center gap-1.5 py-2">
            <span
                class="size-1.5 animate-pulse rounded-full [animation-delay:0ms]"
                style="background-color: var(--ink-muted);"
            ></span>
            <span
                class="size-1.5 animate-pulse rounded-full [animation-delay:150ms]"
                style="background-color: var(--ink-muted);"
            ></span>
            <span
                class="size-1.5 animate-pulse rounded-full [animation-delay:300ms]"
                style="background-color: var(--ink-muted);"
            ></span>
        </div>
    @endif
</div>
