@php
    /**
     * Per-project rendering — the project pill is omitted (you're already
     * inside the project), the status pill is shown inline. The aggregator
     * page includes `task-row-card` directly with different flags.
     */
@endphp

@include('livewire.tasks.partials.task-row-card', [
    'task' => $task,
    'teamMembers' => $teamMembers,
    'allStatuses' => $allStatuses,
    'showProjectPill' => false,
    'showStatusPill' => true,
    'showCheckbox' => true,
    'selectedTaskIds' => $selectedTaskIds ?? [],
])
