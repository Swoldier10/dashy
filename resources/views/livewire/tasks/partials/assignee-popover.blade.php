@php
    $assignedIds = $task->assignees->pluck('id')->all();
    // The row no longer renders an empty placeholder when nobody is assigned —
    // pixel parity with the mockup. To assign from scratch, use the detail
    // drawer (the menu items below still toggle when the popover is open).
@endphp

@if ($task->assignees->isEmpty())
    {{-- intentionally empty so the row stays clean --}}
@else
{{-- close-on-click-inside is off: assigning is a multi-toggle flow — the
     menu stays open while checking/unchecking members (outside/escape closes). --}}
<x-dashy.dropdown align="end" position="bottom" :close-on-click-inside="false">
    <x-slot:trigger>
        <button
            type="button"
            class="rounded-md transition focus:outline-none focus-visible:ring-2"
            style="--tw-ring-color: var(--blue);"
            aria-label="{{ __('Assignees') }}"
            data-test="assignee-trigger-{{ $task->id }}"
        >
            @include('livewire.tasks.partials.assignee-stack', ['task' => $task])
        </button>
    </x-slot:trigger>

    <x-dashy.menu class="!min-w-[280px]">
        <div class="px-2 py-1.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider"
               style="color: var(--ink-dim);">{{ __('Assignees') }}</p>
        </div>

        @forelse ($teamMembers as $member)
            @php $isAssigned = in_array($member->id, $assignedIds, true); @endphp
            <x-dashy.menu.item
                as="button"
                type="button"
                wire:click.stop="toggleAssignee({{ $task->id }}, {{ $member->id }})"
                data-test="assignee-toggle-{{ $task->id }}-{{ $member->id }}"
            >
                <div class="flex w-full items-center gap-2">
                    <x-dashy.avatar
                        :name="$member->name"
                        :initials="$member->initials()"
                        :src="$member->avatar"
                        size="xs"
                    />
                    <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--ink);">
                        {{ $member->name }}
                    </span>
                    @if ($isAssigned)
                        <x-dashy.icon name="check" class="size-4 shrink-0" style="color: var(--blue);" />
                    @endif
                </div>
            </x-dashy.menu.item>
        @empty
            <div class="px-3 py-3 text-sm" style="color: var(--ink-dim);">
                {{ __('No team members.') }}
            </div>
        @endforelse
    </x-dashy.menu>
</x-dashy.dropdown>
@endif
