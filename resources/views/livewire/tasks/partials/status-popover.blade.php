@php
    use App\Domains\Projects\Enums\ProjectStatusCategory;
    $statusesByCategory = $allStatuses->groupBy(fn ($s) => $s->category->value);
    $currentStatus = $task->status;
    $currentColor = $currentStatus?->category?->colorVar() ?? '--ink-dim';
@endphp

<x-dashy.dropdown align="start" position="bottom">
    <x-slot:trigger>
        <button
            type="button"
            wire:click.stop
            class="inline-flex h-5 items-center gap-1 rounded px-1.5 text-[11px] leading-4 font-medium transition focus:outline-none focus-visible:ring-2"
            style="
                background-color: color-mix(in srgb, var({{ $currentColor }}) 14%, transparent);
                color: color-mix(in srgb, var({{ $currentColor }}) 80%, var(--ink));
                --tw-ring-color: var(--blue);
            "
            data-test="status-trigger-{{ $task->id }}"
        >
            <span class="inline-block size-1 shrink-0 rounded-full"
                  style="background-color: var({{ $currentColor }});"></span>
            <span class="truncate">{{ $currentStatus?->name ?? __('No status') }}</span>
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
                    @php $isActive = $statusOption->id === $task->project_status_id; @endphp
                    <x-dashy.menu.item
                        as="button"
                        type="button"
                        wire:click.stop="updateStatus({{ $task->id }}, {{ $statusOption->id }})"
                        data-test="status-option-{{ $task->id }}-{{ $statusOption->id }}"
                    >
                        <div class="flex w-full items-center gap-2">
                            <span class="inline-block size-2 shrink-0 rounded-full"
                                  style="background-color: var({{ $category->colorVar() }});"></span>
                            <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--ink);">
                                {{ $statusOption->name }}
                            </span>
                            @if ($isActive)
                                <x-dashy.icon name="check" class="size-4 shrink-0" style="color: var(--blue);" />
                            @endif
                        </div>
                    </x-dashy.menu.item>
                @endforeach
            @endif
        @endforeach
    </x-dashy.menu>
</x-dashy.dropdown>
