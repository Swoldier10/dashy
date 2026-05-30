<?php

namespace App\Livewire\TimeTracking;

use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\DeleteTimeEntryService;
use App\Domains\TimeTracking\Services\FindTimeEntryService;
use App\Domains\TimeTracking\Services\ListTaskTimeEntriesService;
use App\Domains\TimeTracking\Services\LogManualTimeService;
use App\Domains\TimeTracking\Services\StartTimerService;
use App\Domains\TimeTracking\Services\StopTimerService;
use App\Domains\TimeTracking\Services\UpdateTimeEntryService;
use App\Livewire\Concerns\StopsActiveTimer;
use App\Models\User;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskTimePanel extends Component
{
    use DispatchesDashyUi;
    use StopsActiveTimer;

    public int $taskId = 0;

    public string $manualDuration = '';

    public string $manualNotes = '';

    public ?int $editingEntryId = null;

    public string $editDuration = '';

    public string $editNotes = '';

    /**
     * $taskId === 0 means "no task selected". The panel renders a stable empty
     * <section> so it can stay mounted at the top level of the task-detail
     * drawer instead of nesting inside an @if/@else — Livewire 4's morph
     * crashes when a <livewire:…> child lives inside a conditional block.
     */
    public function mount(int $taskId): void
    {
        $this->taskId = $taskId;
    }

    #[Computed]
    public function task(): ?Task
    {
        if ($this->taskId === 0) {
            return null;
        }

        return app(FindTaskService::class)->execute(Auth::user(), $this->taskId);
    }

    /**
     * @return Collection<int, TimeEntry>
     */
    #[Computed]
    public function entries(): Collection
    {
        $task = $this->task;

        if ($task === null) {
            return new Collection;
        }

        return $this->listResult($task)['entries'];
    }

    /**
     * Entries grouped by user, for the ClickUp-style expandable entries list.
     * Each row: ['user' => User, 'total_seconds' => int, 'entries' => Collection<int, TimeEntry>].
     *
     * Returns a Support\Collection (not Eloquent\Collection) because
     * groupBy()/map()/values() always return the base Support type.
     *
     * @return \Illuminate\Support\Collection<int, array{user: User|null, total_seconds: int, entries: Collection<int, TimeEntry>}>
     */
    #[Computed]
    public function entriesByUser(): \Illuminate\Support\Collection
    {
        return $this->entries
            ->groupBy('user_id')
            ->map(fn ($group): array => [
                'user' => $group->first()->user,
                'total_seconds' => (int) $group->sum('duration_seconds'),
                'entries' => $group,
            ])
            ->values();
    }

    #[Computed]
    public function totalSeconds(): int
    {
        $task = $this->task;

        if ($task === null) {
            return 0;
        }

        return $this->listResult($task)['total_seconds'];
    }

    #[Computed]
    public function runningEntry(): ?TimeEntry
    {
        return $this->entries->firstWhere('ended_at', null);
    }

    #[Computed]
    public function isRunningForCurrentUser(): bool
    {
        $running = $this->runningEntry;

        return $running !== null && (int) $running->user_id === (int) Auth::id();
    }

    /**
     * @return array{entries: Collection<int, TimeEntry>, total_seconds: int}
     */
    private function listResult(Task $task): array
    {
        return app(ListTaskTimeEntriesService::class)->execute(Auth::user(), $task);
    }

    public function startTimer(StartTimerService $service): void
    {
        $task = $this->task;

        if ($task === null) {
            return;
        }

        $service->execute(Auth::user(), $task);

        $this->forgetComputed();
        $this->dispatch('time-entries-updated', taskId: $this->taskId);
        $this->toast('success', __('Timer started.'));
    }

    public function stopTimer(StopTimerService $service): void
    {
        if (! $this->stopActiveTimer($service)) {
            return;
        }

        $this->forgetComputed();
        $this->dispatch('time-entries-updated', taskId: $this->taskId);
        $this->toast('success', __('Timer stopped.'));
    }

    public function logManual(LogManualTimeService $service): void
    {
        $task = $this->task;

        if ($task === null) {
            return;
        }

        $service->execute(Auth::user(), $task, [
            'duration' => $this->manualDuration,
            'notes' => $this->manualNotes !== '' ? $this->manualNotes : null,
        ]);

        $this->resetManualForm();
        $this->forgetComputed();
        $this->dispatch('time-entries-updated', taskId: $this->taskId);
        $this->toast('success', __('Time logged.'));
    }

    public function startEditing(int $entryId): void
    {
        $entry = $this->entries->firstWhere('id', $entryId);

        if ($entry === null || $entry->isRunning()) {
            return;
        }

        $this->editingEntryId = $entryId;
        $this->editDuration = (string) intdiv((int) $entry->duration_seconds, 60).'m';
        $this->editNotes = (string) ($entry->notes ?? '');
    }

    public function cancelEditing(): void
    {
        $this->editingEntryId = null;
        $this->editDuration = '';
        $this->editNotes = '';
    }

    public function saveEntry(int $entryId, UpdateTimeEntryService $service): void
    {
        $entry = app(FindTimeEntryService::class)->execute(Auth::user(), $entryId);

        $service->execute(Auth::user(), $entry, [
            'duration' => $this->editDuration,
            'notes' => $this->editNotes !== '' ? $this->editNotes : null,
        ]);

        $this->cancelEditing();
        $this->forgetComputed();
        $this->dispatch('time-entries-updated', taskId: $this->taskId);
        $this->toast('success', __('Entry updated.'));
    }

    public function deleteEntry(int $entryId, DeleteTimeEntryService $service): void
    {
        $entry = app(FindTimeEntryService::class)->execute(Auth::user(), $entryId);
        $service->execute(Auth::user(), $entry);

        if ($this->editingEntryId === $entryId) {
            $this->cancelEditing();
        }

        $this->forgetComputed();
        $this->dispatch('time-entries-updated', taskId: $this->taskId);
        $this->toast('success', __('Entry deleted.'));
    }

    #[On('time-entries-updated')]
    public function refresh(): void
    {
        $this->forgetComputed();
    }

    private function forgetComputed(): void
    {
        unset(
            $this->entries,
            $this->entriesByUser,
            $this->totalSeconds,
            $this->runningEntry,
            $this->isRunningForCurrentUser,
        );
    }

    private function resetManualForm(): void
    {
        $this->manualDuration = '';
        $this->manualNotes = '';
    }

    public function render()
    {
        return view('livewire.time-tracking.task-time-panel');
    }
}
