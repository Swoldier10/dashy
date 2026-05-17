@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     *
     * Plan card: a checklist of the steps the assistant is about to take.
     * No interaction — purely a transparency surface. Sits at the top of
     * the turn so the user reads it before any tool result lands.
     */
    $status = $card['status'] ?? 'executed';
    $steps = (array) ($card['steps'] ?? []);
    $errors = (array) ($card['validation_errors'] ?? []);
@endphp

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="plan"
    data-mode="structural_auto"
    data-status="{{ $status }}"
>
    <div class="flex items-start gap-3 px-4 py-3">
        <div
            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full"
            style="background-color: var(--surface-3); color: var(--blue);"
        >
            <x-dashy.icon name="map" class="size-4" />
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-[13px] font-semibold uppercase tracking-wide" style="color: var(--ink-muted);">
                {{ __('Plan') }}
            </p>

            @if ($errors !== [])
                <ul class="mt-2 list-disc space-y-0.5 pl-5 text-xs" style="color: var(--state-error);">
                    @foreach ($errors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @elseif ($steps !== [])
                <ol class="mt-2 space-y-1 text-sm" style="color: var(--ink);">
                    @foreach ($steps as $i => $step)
                        <li class="flex items-baseline gap-2">
                            <span
                                class="inline-flex size-5 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--surface-3); color: var(--ink-muted);"
                            >{{ $i + 1 }}</span>
                            <span class="min-w-0">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>
</div>
