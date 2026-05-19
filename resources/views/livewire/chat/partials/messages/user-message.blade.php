<div wire:key="msg-{{ $msg->id }}" class="flex justify-end">
    <div class="flex max-w-[85%] flex-col gap-2">
        @if (! empty($msg->attachments))
            <div class="flex flex-wrap justify-end gap-2">
                @foreach ($msg->attachments as $att)
                    @if (($att['type'] ?? null) === 'image')
                        <a
                            href="{{ $att['url'] ?? '#' }}"
                            target="_blank"
                            rel="noopener"
                            class="block max-w-[240px] overflow-hidden rounded-xl border"
                            style="border-color: var(--border-mid);"
                        >
                            <img
                                src="{{ $att['url'] ?? '' }}"
                                alt="{{ $att['name'] ?? '' }}"
                                class="h-auto w-full"
                                loading="lazy"
                            />
                        </a>
                    @elseif (($att['type'] ?? null) === 'audio')
                        <div class="flex max-w-[320px] flex-col gap-1">
                            @include('livewire.chat.partials.audio-bubble', [
                                'url' => $att['url'] ?? '',
                                'duration' => $att['duration_seconds'] ?? null,
                                'compact' => false,
                            ])
                            @if (! empty($att['transcript']))
                                <p class="px-2 text-xs italic" style="color: var(--ink-muted);">
                                    {{ $att['transcript'] }}
                                </p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if (trim((string) $msg->content) !== '')
            <div
                class="rounded-2xl px-4 py-3 text-[15px] leading-relaxed"
                style="background-color: var(--surface); color: var(--ink);"
            >
                <div class="whitespace-pre-wrap break-words">{{ $msg->content }}</div>
            </div>
        @endif
    </div>
</div>
