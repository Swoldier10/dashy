@php
    use App\Domains\Tasks\Enums\TaskPriority;
    $priorities = [TaskPriority::Urgent, TaskPriority::High, TaskPriority::Normal, TaskPriority::Low];
    $current = $task->priority instanceof TaskPriority ? $task->priority : TaskPriority::tryFrom((string) $task->priority) ?? TaskPriority::Normal;
    $plainText = $plainText ?? false;
@endphp

<x-dashy.dropdown align="end" position="bottom">
    <x-slot:trigger>
        @if ($plainText)
            <button
                type="button"
                class="inline-flex h-5 items-center text-[11px] leading-4 transition focus:outline-none focus-visible:underline hover:underline"
                style="color: var(--ink-muted);"
                aria-label="{{ __('Priority') }}: {{ $current->label() }}"
                data-test="priority-trigger-{{ $task->id }}"
            >
                <span>{{ $current->shortLabel() }}</span>
            </button>
        @else
            <button
                type="button"
                class="inline-flex h-5 items-center gap-1 rounded px-1.5 text-[11px] leading-4 font-medium transition focus:outline-none focus-visible:ring-2"
                style="
                    background-color: color-mix(in srgb, var({{ $current->colorVar() }}) 14%, transparent);
                    color: color-mix(in srgb, var({{ $current->colorVar() }}) 80%, var(--ink));
                    --tw-ring-color: var(--blue);
                "
                aria-label="{{ __('Priority') }}: {{ $current->label() }}"
                data-test="priority-trigger-{{ $task->id }}"
            >
                <span class="inline-block size-1 rounded-full" style="background-color: var({{ $current->colorVar() }});"></span>
                <span>{{ $current->shortLabel() }}</span>
            </button>
        @endif
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
                wire:click="updatePriority({{ $task->id }}, '{{ $option->value }}')"
                data-test="priority-option-{{ $task->id }}-{{ $option->value }}"
            >
                <div class="flex w-full items-center gap-2">
                    <x-dashy.icon name="flag" class="size-4 shrink-0" style="color: var({{ $option->colorVar() }});" />
                    <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--ink);">
                        {{ $option->label() }}
                    </span>
                    @if ($current === $option)
                        <x-dashy.icon name="check" class="size-4 shrink-0" style="color: var(--blue);" />
                    @endif
                </div>
            </x-dashy.menu.item>
        @endforeach
    </x-dashy.menu>
</x-dashy.dropdown>
