@php
    use App\Domains\Projects\Enums\ProjectStatusCategory;
    use App\Domains\Tasks\Enums\TaskPriority;

    /**
     * @var int $selectedCount
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Domains\Projects\Models\ProjectStatus> $statuses
     */
    $statusesByCategory = $statuses->groupBy(fn ($s) => $s->category->value);
    $priorities = [TaskPriority::Urgent, TaskPriority::High, TaskPriority::Normal, TaskPriority::Low];

    $today = now()->toDateString();
    $tomorrow = now()->addDay()->toDateString();
    $nextWeek = now()->addWeek()->toDateString();
    $twoWeeks = now()->addWeeks(2)->toDateString();
    $fourWeeks = now()->addWeeks(4)->toDateString();
    $eightWeeks = now()->addWeeks(8)->toDateString();

    $actionClasses = 'inline-flex min-h-[44px] shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 md:min-h-0';
@endphp

{{-- Always rendered, visibility toggled — the Alpine popover roots must
     initialize on the initial page load. A morph-inserted @if block leaves
     the dropdown toggles unbound (modals/drawers follow the same idiom). --}}
    <div
        wire:key="bulk-toolbar"
        data-test="bulk-actions-toolbar"
        @class([
            'pointer-events-none fixed inset-x-3 bottom-[76px] z-40 flex justify-center md:inset-x-0 lg:left-[280px]',
            'hidden' => $selectedCount === 0,
        ])
        style="padding-bottom: env(safe-area-inset-bottom, 0px);"
    >
        <div
            role="toolbar"
            aria-label="{{ __('Bulk actions') }}"
            class="pointer-events-auto flex w-full max-w-md items-center gap-1 overflow-x-auto rounded-full border p-2 shadow-lg md:w-auto md:max-w-none md:gap-1.5 md:px-3 md:py-1.5"
            style="background-color: var(--surface); border-color: var(--border); box-shadow: 0 10px 30px -12px rgba(var(--ink-rgb), 0.18);"
        >
            {{-- Count + clear --}}
            <span
                class="shrink-0 whitespace-nowrap pl-1.5 text-xs font-medium"
                style="color: var(--ink);"
                data-test="bulk-selected-count"
            >
                {{ __(':count selected', ['count' => $selectedCount]) }}
            </span>
            <button
                type="button"
                wire:click="clearSelection"
                class="{{ $actionClasses }}"
                style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                onmouseover="this.style.color='var(--ink)';"
                onmouseout="this.style.color='var(--ink-muted)';"
                aria-label="{{ __('Clear selection') }}"
                data-test="bulk-clear"
            >
                <x-dashy.icon name="x-mark" class="size-4" />
                <span class="hidden md:inline">{{ __('Clear') }}</span>
            </button>

            <span class="hidden h-5 w-px shrink-0 md:block" style="background-color: var(--border);" aria-hidden="true"></span>

            {{-- Change status --}}
            <x-dashy.dropdown align="center" position="top">
                <x-slot:trigger>
                    <button
                        type="button"
                        class="{{ $actionClasses }}"
                        style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                        aria-label="{{ __('Change status') }}"
                        data-test="bulk-status-trigger"
                    >
                        <x-dashy.icon name="arrow-path" class="size-4" />
                        <span class="hidden md:inline">{{ __('Status') }}</span>
                    </button>
                </x-slot:trigger>

                <x-dashy.menu class="!min-w-[260px]">
                    @foreach (ProjectStatusCategory::cases() as $category)
                        @php $items = $statusesByCategory[$category->value] ?? collect(); @endphp
                        @if ($items->isNotEmpty())
                            <div class="px-2 pt-1.5 pb-1">
                                <p class="text-[10px] font-semibold uppercase tracking-wider"
                                   style="color: var(--ink-dim);">{{ $category->label() }}</p>
                            </div>
                            @foreach ($items as $statusOption)
                                <x-dashy.menu.item
                                    as="button"
                                    type="button"
                                    wire:click="bulkSetStatus({{ $statusOption->id }})"
                                    data-test="bulk-status-option-{{ $statusOption->id }}"
                                >
                                    <div class="flex w-full items-center gap-2">
                                        <span class="inline-block size-2 shrink-0 rounded-full"
                                              style="background-color: var({{ $category->colorVar() }});"></span>
                                        <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--ink);">
                                            {{ $statusOption->name }}
                                        </span>
                                    </div>
                                </x-dashy.menu.item>
                            @endforeach
                        @endif
                    @endforeach
                </x-dashy.menu>
            </x-dashy.dropdown>

            {{-- Set due date. close-on-click-inside is off (the embedded
                 date-picker needs in-panel clicks), so close explicitly when
                 an in-panel action clears the selection and hides the toolbar. --}}
            <x-dashy.dropdown
                align="center"
                position="top"
                :close-on-click-inside="false"
                x-effect="if (! $wire.selectedTaskIds.length) open = false"
            >
                <x-slot:trigger>
                    <button
                        type="button"
                        class="{{ $actionClasses }}"
                        style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                        aria-label="{{ __('Set due date') }}"
                        data-test="bulk-date-trigger"
                    >
                        <x-dashy.icon name="clock" class="size-4" />
                        <span class="hidden md:inline">{{ __('Due date') }}</span>
                    </button>
                </x-slot:trigger>

                <x-dashy.menu class="!min-w-[280px]">
                    <div class="px-2 py-1.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider"
                           style="color: var(--ink-dim);">{{ __('Due date') }}</p>
                    </div>

                    {{-- Custom date first — its calendar opens downward and stays
                         in-viewport above the toolbar when anchored this high. --}}
                    <div class="px-2 pb-1.5">
                        <x-dashy.date-picker
                            wire:model="bulkDueDateInput"
                            on-change="applyBulkCustomDueDate"
                            :placeholder="__('Pick custom date…')"
                            test-id="bulk-date-custom"
                        />
                    </div>

                    <x-dashy.menu.separator />

                    <x-dashy.menu.item as="button" type="button"
                        wire:click="bulkSetDueDate('{{ $today }}')"
                        data-test="bulk-date-today">
                        <span class="text-sm" style="color: var(--ink);">{{ __('Today') }}</span>
                    </x-dashy.menu.item>
                    <x-dashy.menu.item as="button" type="button"
                        wire:click="bulkSetDueDate('{{ $tomorrow }}')"
                        data-test="bulk-date-tomorrow">
                        <span class="text-sm" style="color: var(--ink);">{{ __('Tomorrow') }}</span>
                    </x-dashy.menu.item>
                    <x-dashy.menu.item as="button" type="button"
                        wire:click="bulkSetDueDate('{{ $nextWeek }}')">
                        <span class="text-sm" style="color: var(--ink);">{{ __('Next week') }}</span>
                    </x-dashy.menu.item>
                    <x-dashy.menu.item as="button" type="button"
                        wire:click="bulkSetDueDate('{{ $twoWeeks }}')">
                        <span class="text-sm" style="color: var(--ink);">{{ __('In 2 weeks') }}</span>
                    </x-dashy.menu.item>
                    <x-dashy.menu.item as="button" type="button"
                        wire:click="bulkSetDueDate('{{ $fourWeeks }}')">
                        <span class="text-sm" style="color: var(--ink);">{{ __('In 4 weeks') }}</span>
                    </x-dashy.menu.item>
                    <x-dashy.menu.item as="button" type="button"
                        wire:click="bulkSetDueDate('{{ $eightWeeks }}')">
                        <span class="text-sm" style="color: var(--ink);">{{ __('In 8 weeks') }}</span>
                    </x-dashy.menu.item>

                    <x-dashy.menu.separator />

                    <x-dashy.menu.item as="button" type="button"
                        wire:click="bulkSetDueDate(null)"
                        data-test="bulk-date-clear">
                        <span class="text-sm" style="color: var(--state-error);">{{ __('Clear dates') }}</span>
                    </x-dashy.menu.item>
                </x-dashy.menu>
            </x-dashy.dropdown>

            {{-- Set priority --}}
            <x-dashy.dropdown align="center" position="top">
                <x-slot:trigger>
                    <button
                        type="button"
                        class="{{ $actionClasses }}"
                        style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                        aria-label="{{ __('Set priority') }}"
                        data-test="bulk-priority-trigger"
                    >
                        <x-dashy.icon name="flag" class="size-4" />
                        <span class="hidden md:inline">{{ __('Priority') }}</span>
                    </button>
                </x-slot:trigger>

                <x-dashy.menu class="!min-w-[200px]">
                    <div class="px-2 py-1.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider"
                           style="color: var(--ink-dim);">{{ __('Priority') }}</p>
                    </div>

                    @foreach ($priorities as $option)
                        <x-dashy.menu.item
                            as="button"
                            type="button"
                            wire:click="bulkSetPriority('{{ $option->value }}')"
                            data-test="bulk-priority-option-{{ $option->value }}"
                        >
                            <div class="flex w-full items-center gap-2">
                                <x-dashy.icon name="flag" class="size-4 shrink-0" style="color: var({{ $option->colorVar() }});" />
                                <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--ink);">
                                    {{ $option->label() }}
                                </span>
                            </div>
                        </x-dashy.menu.item>
                    @endforeach
                </x-dashy.menu>
            </x-dashy.dropdown>

            <span class="hidden h-5 w-px shrink-0 md:block" style="background-color: var(--border);" aria-hidden="true"></span>

            {{-- Archive --}}
            <button
                type="button"
                wire:click="bulkArchive"
                class="{{ $actionClasses }}"
                style="color: var(--ink-muted); --tw-ring-color: var(--blue);"
                onmouseover="this.style.color='var(--ink)';"
                onmouseout="this.style.color='var(--ink-muted)';"
                aria-label="{{ __('Archive') }}"
                data-test="bulk-archive"
            >
                <x-dashy.icon name="archive-box-arrow-down" class="size-4" />
                <span class="hidden md:inline">{{ __('Archive') }}</span>
            </button>

            {{-- Delete (confirmed via dialog) --}}
            <button
                type="button"
                wire:click="confirmBulkDelete"
                class="{{ $actionClasses }}"
                style="color: var(--state-error); --tw-ring-color: var(--blue);"
                aria-label="{{ __('Delete') }}"
                data-test="bulk-delete"
            >
                <x-dashy.icon name="trash" class="size-4" />
                <span class="hidden md:inline">{{ __('Delete') }}</span>
            </button>
        </div>
    </div>
