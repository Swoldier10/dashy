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
    data-tool="create_project"
    data-status="{{ $status }}"
>
    {{-- Header --}}
    <div
        class="flex items-center gap-2 border-b px-4 py-2.5 text-xs uppercase tracking-wide"
        style="border-color: var(--border); color: var(--ink-muted);"
    >
        <x-dashy.icon name="folder-plus" class="size-4" style="color: var(--blue);" />
        @if ($isPending)
            {{ __('New project — review before creating') }}
        @elseif ($status === 'created')
            <span style="color: var(--state-success);">{{ __('Project created') }}</span>
        @elseif ($status === 'discarded')
            {{ __('Discarded') }}
        @elseif ($status === 'failed')
            <span style="color: var(--state-error);">{{ __('Could not prepare project') }}</span>
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
            @if ($validationErrors !== [])
                <ul class="list-disc space-y-1 rounded-md border px-4 py-2 pl-8 text-sm"
                    style="border-color: var(--state-error); color: var(--state-error); background-color: rgba(220, 38, 38, 0.06);"
                    data-test="tool-call-validation-errors"
                >
                    @foreach ($validationErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="flex flex-col gap-3 md:flex-row md:items-start md:gap-4">
                <div class="flex shrink-0 flex-col items-center gap-2">
                    @if (! empty($card['logo']))
                        <a
                            href="{{ $card['logo']['url'] }}"
                            target="_blank"
                            rel="noopener"
                            class="block overflow-hidden rounded-lg border"
                            style="border-color: var(--border-mid);"
                        >
                            <img
                                src="{{ $card['logo']['url'] }}"
                                alt="{{ $card['logo']['name'] ?? '' }}"
                                class="block h-20 w-20 object-cover"
                                loading="lazy"
                            />
                        </a>
                    @else
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-lg border"
                            style="border-color: var(--border); color: var(--ink-dim);"
                        >
                            <x-dashy.icon name="photo" class="size-6" />
                        </div>
                    @endif

                    <div class="flex flex-col items-center gap-1 text-xs">
                        <label
                            class="inline-flex min-h-9 cursor-pointer items-center justify-center rounded-full border px-3 py-1 transition"
                            style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                            onmouseover="this.style.color='var(--ink)'; this.style.borderColor='var(--border-strong)';"
                            onmouseout="this.style.color='var(--ink-muted)'; this.style.borderColor='var(--border-mid)';"
                        >
                            {{ empty($card['logo']) ? __('Add logo') : __('Replace') }}
                            <input
                                type="file"
                                class="sr-only"
                                accept="image/*"
                                wire:model="toolCallLogoUploads.{{ $message->id }}"
                            />
                        </label>
                        @if (! empty($card['logo']))
                            <button
                                type="button"
                                wire:click="clearToolCallLogo({{ $message->id }})"
                                class="text-xs italic underline-offset-2 hover:underline"
                                style="color: var(--ink-dim);"
                            >
                                {{ __('Remove') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="min-w-0 flex-1 space-y-3">
                    <x-dashy.input
                        :label="__('Name')"
                        wire:model="{{ $editKey }}.name"
                        maxlength="80"
                    />

                    @if (! empty($card['available_teams']))
                        <x-dashy.select :label="__('Team')" wire:model="{{ $editKey }}.team_id">
                            @foreach ($card['available_teams'] as $t)
                                <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                            @endforeach
                        </x-dashy.select>
                    @endif
                </div>
            </div>

            <x-dashy.textarea
                :label="__('Description')"
                wire:model="{{ $editKey }}.description"
                :rows="3"
                maxlength="2000"
            />

            @if (! empty($card['default_statuses']))
                <div class="space-y-1.5">
                    <div class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                        {{ __('Default columns') }}
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($card['default_statuses'] as $statusName)
                            <span
                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs"
                                style="border-color: var(--border-mid); color: var(--ink);"
                            >
                                {{ $statusName }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- Read-only body (created / discarded) --}}
        <div class="space-y-3 px-4 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:gap-4">
                @if (! empty($card['logo']))
                    <a
                        href="{{ $card['logo']['url'] }}"
                        target="_blank"
                        rel="noopener"
                        class="block shrink-0 overflow-hidden rounded-lg border"
                        style="border-color: var(--border-mid);"
                    >
                        <img
                            src="{{ $card['logo']['url'] }}"
                            alt="{{ $card['logo']['name'] ?? '' }}"
                            class="block h-16 w-16 object-cover sm:h-20 sm:w-20"
                            loading="lazy"
                        />
                    </a>
                @endif

                <div class="min-w-0 flex-1 space-y-1">
                    @if (! empty($card['project_name']))
                        <div class="font-medium text-[15px]" style="color: var(--ink);">
                            {{ $card['project_name'] }}
                        </div>
                    @endif

                    @if (! empty($card['team']))
                        <div class="flex items-center gap-2 text-sm">
                            <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                                {{ __('Team') }}
                            </span>
                            <span style="color: var(--ink);">{{ $card['team']['name'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @if (! empty($card['description']))
                <p class="whitespace-pre-wrap text-sm" style="color: var(--ink-muted);">{{ $card['description'] }}</p>
            @endif

            @if (! empty($card['default_statuses']))
                <div class="space-y-1.5">
                    <div class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);">
                        {{ __('Default columns') }}
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($card['default_statuses'] as $statusName)
                            <span
                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs"
                                style="border-color: var(--border-mid); color: var(--ink);"
                            >
                                {{ $statusName }}
                            </span>
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
                style="background-color: var(--blue); color: white;"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'"
                data-test="confirm-tool-call"
            >
                <x-dashy.icon name="check" class="size-4" />
                {{ __('Create project') }}
            </button>
        @elseif ($status === 'created' && ! empty($card['created_project_id']))
            <a
                href="{{ route('tasks.show', $card['created_project_id']) }}"
                class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm transition"
                style="border-color: var(--border-mid); color: var(--ink); background-color: transparent;"
                onmouseover="this.style.borderColor='var(--border-strong)';"
                onmouseout="this.style.borderColor='var(--border-mid)';"
            >
                {{ __('Open project') }}
                <x-dashy.icon name="arrow-up-right" class="size-3.5" />
            </a>
        @elseif ($status === 'discarded')
            <span class="text-xs italic" style="color: var(--ink-dim);">
                {{ __('No project was created.') }}
            </span>
        @endif
    </div>
</div>
