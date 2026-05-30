@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     */
    $status = $card['status'] ?? 'pending';
    $isPending = $status === 'pending';
    $editKey = 'toolCallEdits.'.$message->id;
    $validationErrors = (array) ($card['validation_errors'] ?? []);
@endphp

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="create_task"
    data-status="{{ $status }}"
>
    {{-- Header --}}
    <div
        class="flex items-center gap-2 border-b px-4 py-2.5 text-xs uppercase tracking-wide"
        style="border-color: var(--border); color: var(--ink-muted);"
    >
        <x-dashy.icon name="check-badge" class="size-4" style="color: var(--blue);" />
        @if ($isPending)
            {{ __('New task — review before creating') }}
        @elseif ($status === 'created')
            <span style="color: var(--state-success);">{{ __('Task created') }}</span>
        @elseif ($status === 'discarded')
            {{ __('Discarded') }}
        @elseif ($status === 'failed')
            <span style="color: var(--state-error);">{{ __('Could not prepare task') }}</span>
        @endif
    </div>

    @if ($status === 'failed')
        <div class="px-4 py-4">
            <ul class="list-disc space-y-1 pl-5 text-sm" style="color: var(--ink);">
                @foreach ($validationErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @elseif ($isPending)
        {{-- Editable body --}}
        <div class="space-y-3 px-4 py-4">
            @include('livewire.chat.partials.tool-cards.validation-errors')

            <x-dashy.input
                :label="__('Name')"
                wire:model="{{ $editKey }}.name"
                maxlength="200"
            />

            <x-dashy.textarea
                :label="__('Description')"
                wire:model="{{ $editKey }}.description"
                :rows="4"
                maxlength="5000"
            />

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @if (! empty($card['project']))
                    <div class="space-y-1.5">
                        <span class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                            {{ __('Project') }}
                        </span>
                        <p class="text-sm" style="color: var(--ink);">{{ $card['project']['name'] }}</p>
                    </div>
                @endif

                @if (! empty($card['available_statuses']))
                    <x-dashy.select :label="__('Status')" wire:model="{{ $editKey }}.status_id">
                        @foreach ($card['available_statuses'] as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                        @endforeach
                    </x-dashy.select>
                @endif

                <x-dashy.select :label="__('Priority')" wire:model="{{ $editKey }}.priority">
                    @foreach ($card['available_priorities'] as $p)
                        <option value="{{ $p['value'] }}">{{ $p['label'] }}</option>
                    @endforeach
                </x-dashy.select>

                <x-dashy.input
                    type="date"
                    :label="__('Start')"
                    wire:model="{{ $editKey }}.start_date"
                />

                <x-dashy.input
                    type="date"
                    :label="__('Due')"
                    wire:model="{{ $editKey }}.end_date"
                />
            </div>

            @if (! empty($card['available_assignees']))
                <div class="space-y-1.5">
                    <span class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                        {{ __('Assignees') }}
                    </span>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($card['available_assignees'] as $assignee)
                            <x-dashy.checkbox
                                wire:model="{{ $editKey }}.assignee_user_ids"
                                :value="$assignee['id']"
                                :label="$assignee['name']"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            @if (! empty($card['images']))
                <div class="space-y-1.5">
                    <div class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                        {{ __('Images') }}
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($card['images'] as $image)
                            <a
                                href="{{ $image['url'] }}"
                                target="_blank"
                                rel="noopener"
                                class="block overflow-hidden rounded-lg border"
                                style="border-color: var(--border-mid);"
                            >
                                <img
                                    src="{{ $image['url'] }}"
                                    alt="{{ $image['name'] ?? '' }}"
                                    class="block h-20 w-20 object-cover sm:h-24 sm:w-24"
                                    loading="lazy"
                                />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- Read-only body (created / discarded) --}}
        <div class="space-y-3 px-4 py-4">
            @if (! empty($card['task_name']))
                <div class="font-medium text-[15px]" style="color: var(--ink);">
                    {{ $card['task_name'] }}
                </div>
            @endif

            @if (! empty($card['description']))
                <p class="whitespace-pre-wrap text-sm" style="color: var(--ink-muted);">{{ $card['description'] }}</p>
            @endif

            <div class="grid grid-cols-1 gap-x-6 gap-y-2 text-sm md:grid-cols-2">
                @if (! empty($card['project']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                            {{ __('Project') }}
                        </span>
                        <span style="color: var(--ink);">{{ $card['project']['name'] }}</span>
                    </div>
                @endif

                @if (! empty($card['task_status']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                            {{ __('Status') }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs"
                            style="background-color: color-mix(in srgb, var({{ $card['task_status']['color_var'] }}) 14%, transparent); color: var({{ $card['task_status']['color_var'] }});"
                        >
                            {{ $card['task_status']['name'] }}
                        </span>
                    </div>
                @endif

                @if (! empty($card['priority']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                            {{ __('Priority') }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs"
                            style="background-color: color-mix(in srgb, var({{ $card['priority']['color_var'] }}) 14%, transparent); color: var({{ $card['priority']['color_var'] }});"
                        >
                            {{ $card['priority']['label'] }}
                        </span>
                    </div>
                @endif

                @if (! empty($card['start_date']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                            {{ __('Start') }}
                        </span>
                        <span style="color: var(--ink);">{{ $card['start_date'] }}</span>
                    </div>
                @endif

                @if (! empty($card['end_date']))
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                            {{ __('Due') }}
                        </span>
                        <span style="color: var(--ink);">{{ $card['end_date'] }}</span>
                    </div>
                @endif

                @if (! empty($card['assignees']))
                    <div class="flex items-start gap-2 md:col-span-2">
                        <span class="shrink-0 pt-0.5 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                            {{ __('Assignees') }}
                        </span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($card['assignees'] as $assignee)
                                <span
                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs"
                                    style="border-color: var(--border-mid); color: var(--ink);"
                                >
                                    {{ $assignee['name'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            @if (! empty($card['images']))
                <div class="space-y-1.5">
                    <div class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                        {{ __('Images') }}
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($card['images'] as $image)
                            <a
                                href="{{ $image['url'] }}"
                                target="_blank"
                                rel="noopener"
                                class="block overflow-hidden rounded-lg border"
                                style="border-color: var(--border-mid);"
                            >
                                <img
                                    src="{{ $image['url'] }}"
                                    alt="{{ $image['name'] ?? '' }}"
                                    class="block h-20 w-20 object-cover sm:h-24 sm:w-24"
                                    loading="lazy"
                                />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Footer / actions --}}
    <div
        class="flex flex-wrap items-center justify-end gap-2 border-t px-4 py-3"
        style="border-color: var(--border);"
    >
        @if ($isPending)
            <button
                type="button"
                wire:click="discardToolCall({{ $message->id }})"
                wire:loading.attr="disabled"
                class="rounded-full border px-4 py-1.5 text-sm transition"
                style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                onmouseover="this.style.color='var(--ink)'; this.style.borderColor='var(--border-strong)';"
                onmouseout="this.style.color='var(--ink-muted)'; this.style.borderColor='var(--border-mid)';"
                data-test="discard-tool-call"
            >
                {{ __('Discard') }}
            </button>
            <button
                type="button"
                wire:click="confirmToolCall({{ $message->id }})"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-medium transition"
                style="background-color: var(--blue); color: var(--surface);"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'"
                data-test="confirm-tool-call"
            >
                <x-dashy.icon name="check" class="size-4" />
                {{ __('Create task') }}
            </button>
        @elseif ($status === 'created' && ! empty($card['project']))
            <a
                href="{{ route('tasks.show', $card['project']['id']) }}"
                class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm transition"
                style="border-color: var(--border-mid); color: var(--ink); background-color: transparent;"
                onmouseover="this.style.borderColor='var(--border-strong)';"
                onmouseout="this.style.borderColor='var(--border-mid)';"
            >
                {{ __('Open in project') }}
                <x-dashy.icon name="arrow-up-right" class="size-3.5" />
            </a>
        @elseif ($status === 'discarded')
            <span class="text-xs italic" style="color: var(--ink-dim);">
                {{ __('No task was created.') }}
            </span>
        @endif
    </div>
</div>
