@php
    $assignees = $task->assignees;
    $visible = $assignees->take(3);
    $extra = max(0, $assignees->count() - $visible->count());
@endphp

<div class="flex items-center" data-test="assignee-stack-{{ $task->id }}">
    @if ($assignees->isEmpty())
        <span
            class="flex size-7 items-center justify-center rounded-full border border-dashed"
            style="border-color: var(--border-strong); color: var(--ink-dim);"
            aria-label="{{ __('No assignees') }}"
        >
            <x-dashy.icon name="user" class="size-3.5" />
        </span>
    @else
        @foreach ($visible as $i => $member)
            <span
                class="relative {{ $i === 0 ? '' : '-ml-2' }}"
                style="z-index: {{ 10 - $i }};"
            >
                <x-dashy.avatar
                    :name="$member->name"
                    :initials="$member->initials()"
                    :src="$member->avatar"
                    size="xs"
                    class="ring-2"
                    style="--tw-ring-color: var(--bg);"
                />
            </span>
        @endforeach
        @if ($extra > 0)
            <span
                class="-ml-2 flex size-6 items-center justify-center rounded-full text-[10px] font-semibold ring-2"
                style="background-color: var(--surface-2); color: var(--ink); --tw-ring-color: var(--bg);"
                aria-label="{{ __(':n more assignees', ['n' => $extra]) }}"
            >+{{ $extra }}</span>
        @endif
    @endif
</div>
