@php
    use App\Domains\Chat\Enums\MessageRole;
    use App\Domains\Chat\Services\MarkdownRenderer;
    $markdown = app(MarkdownRenderer::class);
@endphp

<div class="dashy-chat flex h-full min-h-0 flex-1 flex-col" wire:key="chat-panel">
    @if (! $this->isCodexConnected)
        {{-- ──── Connect Codex empty state ──── --}}
        <div class="m-auto flex max-w-md flex-col items-center gap-6 p-10 text-center">
            <x-dashy.icon name="sparkles" class="size-10" style="color: var(--accent);" />
            <div class="space-y-2">
                <h2 class="font-display text-3xl" style="color: var(--ink);">
                    {{ __('Connect Codex to start chatting') }}
                </h2>
                <p class="text-sm" style="color: var(--ink-muted);">
                    {{ __('Authorise Codex once and your conversations stream straight from the LLM.') }}
                </p>
            </div>
            <button
                type="button"
                x-on:click="$dispatch('open-connect-codex')"
                class="flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-medium transition"
                style="background-color: var(--blue); color: white;"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'"
                data-test="connect-codex-from-chat"
            >
                <x-dashy.icon name="link" class="size-4" />
                {{ __('Connect Codex') }}
            </button>
        </div>
    @elseif ($this->activeChat === null)
        {{-- ──── Empty greeting state ──── --}}
        @php
            $pill = $this->dateTimePill;
            $summary = $this->tomorrowSummary;
        @endphp
        <div class="flex flex-1 flex-col overflow-y-auto">
            <div class="m-auto flex w-full max-w-3xl flex-col items-center justify-center gap-5 px-4 py-10 sm:gap-7 sm:px-6">
                {{-- Date/time pill --}}
                <div
                    class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-wider"
                    style="background-color: var(--surface); border-color: var(--border); color: var(--ink-muted);"
                    data-test="chat-date-pill"
                >
                    <span class="dashy-pulse" aria-hidden="true"></span>
                    <span>{{ $pill['date'] }}</span>
                    <span aria-hidden="true">·</span>
                    <span>{{ $pill['time'] }}</span>
                </div>

                {{-- Greeting --}}
                <h1
                    class="text-center font-display text-3xl font-normal leading-tight sm:text-4xl md:text-5xl"
                    style="color: var(--ink); letter-spacing: -0.01em;"
                    data-test="chat-greeting"
                >
                    {{ $this->greeting }}.
                </h1>

                {{-- Subtitle with tomorrow counts --}}
                <p class="max-w-xl text-center text-sm leading-relaxed sm:text-base" style="color: var(--ink-muted);" data-test="chat-subtitle">
                    {{ __('You have') }}
                    <strong style="color: var(--ink);">{{ trans_choice('{0} no meetings|{1} :count meeting|[2,*] :count meetings', $summary['meetings'], ['count' => $summary['meetings']]) }}</strong>
                    {{ __('and') }}
                    <strong style="color: var(--ink);">{{ trans_choice('{0} no tasks|{1} :count task|[2,*] :count tasks', $summary['tasks'], ['count' => $summary['tasks']]) }}</strong>
                    {{ __('on deck for tomorrow. Want me to help you prep?') }}
                </p>

                {{-- Composer --}}
                <div class="w-full">
                    @include('livewire.chat.partials.composer', ['large' => true])
                </div>
            </div>
        </div>
    @else
        {{-- ──── Active chat: thread + pinned composer ──── --}}
        <div
            class="flex-1 overflow-y-auto"
            wire:key="thread-{{ $activeChatId }}"
            x-data
            x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
            x-on:livewire-update.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
                <div class="space-y-8">
                    @foreach ($this->threadMessages as $msg)
                        @if ($msg->role === MessageRole::User)
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
                        @else
                            <div
                                wire:key="msg-{{ $msg->id }}"
                                class="space-y-3"
                                style="color: var(--ink);"
                            >
                                @if (trim((string) $msg->content) !== '')
                                    <div class="dashy-prose text-[15px] leading-relaxed">
                                        {!! $markdown->render($msg->content) !!}
                                    </div>
                                @endif

                                @if ($msg->tool_call !== null)
                                    @include('livewire.chat.partials.tool-call-card', [
                                        'message' => $msg,
                                        'card' => $this->toolCardFor($msg),
                                    ])
                                @endif
                            </div>
                        @endif
                    @endforeach

                    {{-- Streaming bubble --}}
                    @if ($streamingAssistant !== '' || $isThinking)
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
                    @endif
                </div>
            </div>
        </div>

        {{-- Pinned composer --}}
        <div class="shrink-0 px-3 pb-4 pt-2 sm:px-4 sm:pb-5">
            <div class="mx-auto w-full max-w-3xl">
                @include('livewire.chat.partials.composer', ['large' => false])
                <p class="mt-2 text-center text-xs" style="color: var(--ink-dim);">
                    {{ __('Codex can make mistakes. Verify important details.') }}
                </p>
            </div>
        </div>
    @endif

    {{-- Markdown prose styling for assistant replies --}}
    <style>
        .dashy-prose > * + * { margin-top: 0.85rem; }
        .dashy-prose h1, .dashy-prose h2, .dashy-prose h3, .dashy-prose h4 {
            font-family: 'Fraunces', ui-serif, Georgia, serif;
            font-weight: 500;
            line-height: 1.25;
            margin-top: 1.5rem;
            color: var(--ink);
            letter-spacing: -0.01em;
        }
        .dashy-prose h1 { font-size: 1.6rem; }
        .dashy-prose h2 { font-size: 1.3rem; }
        .dashy-prose h3 { font-size: 1.1rem; }
        .dashy-prose p { line-height: 1.7; }
        .dashy-prose strong { font-weight: 600; color: var(--ink); }
        .dashy-prose em { font-style: italic; }
        .dashy-prose a { color: var(--blue); text-decoration: underline; text-underline-offset: 2px; }
        .dashy-prose ul, .dashy-prose ol { padding-left: 1.5rem; }
        .dashy-prose ul { list-style: disc; }
        .dashy-prose ol { list-style: decimal; }
        .dashy-prose li { margin-top: 0.25rem; line-height: 1.6; }
        .dashy-prose li > p { margin: 0; }
        .dashy-prose blockquote {
            border-left: 3px solid var(--border-mid);
            padding-left: 1rem;
            color: var(--ink-muted);
            font-style: italic;
        }
        .dashy-prose code {
            font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
            font-size: 0.9em;
            background: var(--surface-2);
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            color: var(--ink);
        }
        .dashy-prose pre {
            background: var(--surface-2);
            color: var(--ink);
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            font-size: 0.9rem;
            line-height: 1.55;
            border: 1px solid var(--border-mid);
        }
        .dashy-prose pre code {
            background: transparent;
            padding: 0;
            border-radius: 0;
            color: inherit;
        }
        .dashy-prose hr {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.5rem 0;
        }
        .dashy-prose table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .dashy-prose th, .dashy-prose td {
            border: 1px solid var(--border-mid);
            padding: 0.5rem 0.75rem;
            text-align: left;
        }
        .dashy-prose th {
            background: var(--surface-2);
            font-weight: 600;
            color: var(--ink);
        }
        .dashy-composer-box {
            background-color: var(--bg-deep);
            border-color: var(--border-mid);
            box-shadow:
                0 1px 2px rgba(var(--ink-rgb), 0.04),
                0 10px 28px -12px rgba(var(--ink-rgb), 0.12);
        }
        .dashy-composer-box.is-dragging { border-color: var(--accent); }
        .dashy-chat .dashy-composer-editor:focus { outline: none; box-shadow: none; }
        .dashy-chat .dashy-mention {
            display: inline-flex;
            align-items: center;
            padding: 1px 8px;
            border-radius: 9999px;
            background: rgba(89, 146, 198, 0.18);
            color: var(--blue);
            font-weight: 500;
            font-size: 0.92em;
            line-height: 1.4;
            user-select: none;
            white-space: nowrap;
            vertical-align: baseline;
        }
        .dashy-chat *::-webkit-scrollbar { width: 8px; height: 8px; }
        .dashy-chat *::-webkit-scrollbar-track { background: transparent; }
        .dashy-chat *::-webkit-scrollbar-thumb {
            background: var(--border-mid);
            border-radius: 9999px;
        }
        .dashy-chat *::-webkit-scrollbar-thumb:hover { background: var(--border-strong); }
    </style>
</div>
