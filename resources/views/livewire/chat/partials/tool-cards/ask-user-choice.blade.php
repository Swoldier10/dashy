@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     */
    $status = $card['status'] ?? 'pending';
@endphp

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="ask_user_choice"
    data-status="{{ $status }}"
>
    @if ($status === 'failed')
        <div class="px-4 py-4">
            <ul class="list-disc space-y-1 pl-5 text-sm" style="color: var(--ink);">
                @foreach ($card['validation_errors'] ?? [] as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="space-y-3 px-4 py-4">
            @if (! empty($card['question']))
                <p class="text-[15px]" style="color: var(--ink);">{{ $card['question'] }}</p>
            @endif

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                @foreach ($card['options'] as $index => $option)
                    @php
                        $isChosen = $card['chosen_index'] === $index;
                        $isPending = $status === 'pending';
                    @endphp

                    @if ($isPending)
                        <button
                            type="button"
                            wire:click="answerChoice({{ $message->id }}, {{ $index }})"
                            wire:loading.attr="disabled"
                            class="inline-flex min-h-11 items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition sm:min-h-9 sm:py-1.5"
                            style="border-color: var(--border-mid); color: var(--ink); background-color: transparent;"
                            onmouseover="this.style.borderColor='var(--blue)'; this.style.color='var(--blue)';"
                            onmouseout="this.style.borderColor='var(--border-mid)'; this.style.color='var(--ink)';"
                            data-test="choice-option"
                            data-index="{{ $index }}"
                        >
                            {{ $option }}
                        </button>
                    @else
                        <span
                            class="inline-flex min-h-11 items-center justify-center rounded-full border px-4 py-2 text-sm font-medium sm:min-h-9 sm:py-1.5"
                            @if ($isChosen)
                                style="border-color: var(--blue); color: var(--blue); background-color: rgba(89, 146, 198, 0.08);"
                                data-test="choice-option-chosen"
                            @else
                                style="border-color: var(--border-mid); color: var(--ink-dim); background-color: transparent; opacity: 0.6;"
                            @endif
                        >
                            {{ $option }}
                        </span>
                    @endif
                @endforeach
            </div>

            @if ($status === 'answered' && ! empty($card['chosen_label']))
                <p class="text-xs italic" style="color: var(--ink-dim);">
                    {{ __('You chose :choice.', ['choice' => $card['chosen_label']]) }}
                </p>
            @endif
        </div>
    @endif
</div>
