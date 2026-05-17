<?php

namespace App\Livewire;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Services\CreateEventService;
use App\Domains\Calendar\Services\DeleteEventService;
use App\Domains\Calendar\Services\FindEventService;
use App\Domains\Calendar\Services\ListEventsForUserInRangeService;
use App\Domains\Calendar\Services\ListScheduledTasksForUserInRangeService;
use App\Domains\Calendar\Services\MoveEventService;
use App\Domains\Calendar\Services\ResizeEventService;
use App\Domains\Calendar\Services\UpdateEventService;
use App\Support\Concerns\DispatchesDashyUi;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Calendar extends Component
{
    use DispatchesDashyUi;

    #[Url(as: 'view', keep: false)]
    public string $view = 'week';

    #[Url(as: 'date', keep: false)]
    public string $anchor = '';

    public ?int $detailEventId = null;

    public bool $drawerCreateMode = false;

    public string $formTitle = '';

    public string $formDescription = '';

    public string $formStartAt = '';

    public string $formEndAt = '';

    public bool $formIsAllDay = false;

    public string $formColor = 'danube';

    public string $formLocation = '';

    public string $formRecurrenceFreq = 'none';

    public string $formRecurrenceUntil = '';

    public function mount(): void
    {
        if (! in_array($this->view, ['month', 'week', 'day'], true)) {
            $this->view = 'week';
        }

        if ($this->anchor === '' || ! strtotime($this->anchor)) {
            $this->anchor = CarbonImmutable::now()->toDateString();
        }
    }

    public function anchorDate(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->anchor);
    }

    #[Computed]
    public function rangeStart(): CarbonImmutable
    {
        return match ($this->view) {
            'month' => $this->anchorDate()->startOfMonth()->startOfWeek(CarbonImmutable::MONDAY),
            'week' => $this->anchorDate()->startOfWeek(CarbonImmutable::MONDAY),
            default => $this->anchorDate()->startOfDay(),
        };
    }

    #[Computed]
    public function rangeEnd(): CarbonImmutable
    {
        return match ($this->view) {
            'month' => $this->anchorDate()->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY),
            'week' => $this->anchorDate()->endOfWeek(CarbonImmutable::SUNDAY),
            default => $this->anchorDate()->endOfDay(),
        };
    }

    /** @return list<CarbonImmutable> */
    #[Computed]
    public function days(): array
    {
        $days = [];
        $cursor = $this->rangeStart;
        $end = $this->rangeEnd;

        while ($cursor->lte($end)) {
            $days[] = $cursor;
            $cursor = $cursor->addDay();
        }

        return $days;
    }

    /**
     * Sidebar mini-month grid: always 6 rows × 7 cells, Monday-first
     * (matches FullCalendar's firstDay: 1 in fullcalendar-instance.js).
     *
     * @return array<int, array<int, array{date: CarbonImmutable, inMonth: bool, isToday: bool, isAnchor: bool}>>
     */
    #[Computed]
    public function miniMonthWeeks(): array
    {
        $anchor = $this->anchorDate();
        $today = CarbonImmutable::now()->startOfDay();
        $start = $anchor->startOfMonth()->startOfWeek(CarbonImmutable::MONDAY);

        $weeks = [];
        for ($w = 0; $w < 6; $w++) {
            $row = [];
            for ($d = 0; $d < 7; $d++) {
                $date = $start->addDays($w * 7 + $d);
                $row[] = [
                    'date' => $date,
                    'inMonth' => $date->isSameMonth($anchor),
                    'isToday' => $date->isSameDay($today),
                    'isAnchor' => $date->isSameDay($anchor),
                ];
            }
            $weeks[] = $row;
        }

        return $weeks;
    }

    #[Computed]
    public function miniMonthLabel(): string
    {
        return $this->anchorDate()->translatedFormat('F Y');
    }

    /** @return list<\App\Domains\Calendar\DTOs\EventOccurrence> */
    #[Computed]
    public function occurrences(): array
    {
        return app(ListEventsForUserInRangeService::class)
            ->execute(Auth::user(), $this->rangeStart, $this->rangeEnd);
    }

    /** @return Collection<int, \App\Domains\Tasks\Models\Task> */
    #[Computed]
    public function scheduledTasks(): Collection
    {
        return app(ListScheduledTasksForUserInRangeService::class)
            ->execute(Auth::user(), $this->rangeStart, $this->rangeEnd);
    }

    #[Computed]
    public function displayTitle(): string
    {
        $anchor = $this->anchorDate();
        $now = CarbonImmutable::now();
        $today = $now->startOfDay();

        return match ($this->view) {
            'day' => match (true) {
                $anchor->isSameDay($today) => __('Today'),
                $anchor->isSameDay($today->addDay()) => __('Tomorrow'),
                $anchor->isSameDay($today->subDay()) => __('Yesterday'),
                default => $anchor->translatedFormat('l, F j'),
            },
            'month' => $anchor->isSameMonth($today)
                ? __('This month')
                : $anchor->translatedFormat('F Y'),
            default => match (true) {
                $anchor->isSameWeek($today) => __('This week'),
                $anchor->isSameWeek($today->addWeek()) => __('Next week'),
                $anchor->isSameWeek($today->subWeek()) => __('Last week'),
                default => $this->rangeStart->translatedFormat('M j').' – '.$this->rangeEnd->translatedFormat('M j, Y'),
            },
        };
    }

    public function setView(string $view): void
    {
        if (in_array($view, ['month', 'week', 'day'], true)) {
            $this->view = $view;
        }
    }

    public function prev(): void
    {
        $this->anchor = match ($this->view) {
            'month' => $this->anchorDate()->subMonth()->toDateString(),
            'week' => $this->anchorDate()->subWeek()->toDateString(),
            default => $this->anchorDate()->subDay()->toDateString(),
        };
    }

    public function next(): void
    {
        $this->anchor = match ($this->view) {
            'month' => $this->anchorDate()->addMonth()->toDateString(),
            'week' => $this->anchorDate()->addWeek()->toDateString(),
            default => $this->anchorDate()->addDay()->toDateString(),
        };
    }

    public function goToday(): void
    {
        $this->anchor = CarbonImmutable::now()->toDateString();
    }

    public function setAnchor(string $date): void
    {
        if (strtotime($date) === false) {
            return;
        }

        $this->anchor = CarbonImmutable::parse($date)->toDateString();
    }

    public function openCreate(?string $startAt = null, ?string $endAt = null): void
    {
        $this->resetForm();
        $this->drawerCreateMode = true;

        $start = $startAt ? CarbonImmutable::parse($startAt) : $this->anchorDate()->setTime(9, 0, 0);
        $end = $endAt ? CarbonImmutable::parse($endAt) : $start->addHour();

        $this->formStartAt = $start->format('Y-m-d\TH:i');
        $this->formEndAt = $end->format('Y-m-d\TH:i');

        $this->openModal('calendar-event-detail');
    }

    public function createEvent(string $startAt, string $endAt): void
    {
        // Called by the JS drag-to-select gesture. Opens the create drawer
        // pre-filled with the dragged range.
        $this->openCreate($startAt, $endAt);
    }

    public function submitCreate(CreateEventService $svc): void
    {
        $event = $svc->execute(Auth::user(), [
            'title' => $this->formTitle,
            'description' => $this->formDescription !== '' ? $this->formDescription : null,
            'start_at' => $this->normalizeFormDate($this->formStartAt),
            'end_at' => $this->normalizeFormDate($this->formEndAt),
            'is_all_day' => $this->formIsAllDay,
            'color' => $this->formColor,
            'location' => $this->formLocation !== '' ? $this->formLocation : null,
            'recurrence_freq' => $this->formRecurrenceFreq,
            'recurrence_until' => $this->formRecurrenceUntil !== '' ? $this->formRecurrenceUntil : null,
        ]);

        $this->detailEventId = $event->id;
        $this->drawerCreateMode = false;
        $this->closeModal('calendar-event-detail');
        $this->toast('success', __('Event created.'));
        $this->dispatch('calendar-events-changed');
    }

    /**
     * Returns FullCalendar event objects for the date range FC requests.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCalendarPayload(string $startIso, string $endIso): array
    {
        $start = CarbonImmutable::parse($startIso);
        $end = CarbonImmutable::parse($endIso);
        $user = Auth::user();

        $occurrences = app(ListEventsForUserInRangeService::class)->execute($user, $start, $end);
        $tasks = app(ListScheduledTasksForUserInRangeService::class)->execute($user, $start, $end);

        $payload = array_map(fn ($occ) => [
            'id' => 'event-'.$occ->key(),
            'title' => $occ->event->title,
            'start' => $occ->startAt->toIso8601String(),
            'end' => $occ->endAt->toIso8601String(),
            'allDay' => (bool) $occ->event->is_all_day,
            'classNames' => ['fc-event-'.$occ->event->color->value],
            'extendedProps' => ['type' => 'event', 'eventId' => $occ->event->id],
        ], $occurrences);

        foreach ($tasks as $task) {
            if ($task->start_date === null) {
                continue;
            }
            $payload[] = [
                'id' => 'task-'.$task->id,
                'title' => $task->name,
                'start' => $task->start_date->toIso8601String(),
                'end' => $task->end_date?->toIso8601String(),
                'allDay' => true,
                'editable' => false,
                'classNames' => ['fc-event-task'],
                'extendedProps' => ['type' => 'task', 'taskId' => $task->id],
            ];
        }

        return array_values($payload);
    }

    public function moveEvent(int $eventId, string $newStartAt, MoveEventService $svc): void
    {
        $svc->execute(Auth::user(), $eventId, $newStartAt);
        $this->dispatch('calendar-events-changed');
    }

    public function resizeEvent(int $eventId, string $newEndAt, ResizeEventService $svc): void
    {
        $svc->execute(Auth::user(), $eventId, $newEndAt);
        $this->dispatch('calendar-events-changed');
    }

    public function openEventDetail(int $eventId): void
    {
        $event = app(FindEventService::class)->execute(Auth::user(), $eventId);

        $this->detailEventId = $event->id;
        $this->drawerCreateMode = false;
        $this->formTitle = $event->title;
        $this->formDescription = (string) ($event->description ?? '');
        $this->formStartAt = $event->start_at->format('Y-m-d\TH:i');
        $this->formEndAt = $event->end_at->format('Y-m-d\TH:i');
        $this->formIsAllDay = (bool) $event->is_all_day;
        $this->formColor = $event->color->value;
        $this->formLocation = (string) ($event->location ?? '');
        $this->formRecurrenceFreq = $event->recurrence_freq->value;
        $this->formRecurrenceUntil = $event->recurrence_until?->toDateString() ?? '';

        $this->openModal('calendar-event-detail');
    }

    public function closeEventDetail(): void
    {
        // Intentionally keep $detailEventId so reopening the same event after
        // closing avoids the morph-null bug documented in the tasks page
        // (resources/views/pages/tasks/⚡show.blade.php:288-298). The drawer is
        // hidden by Alpine x-show; openEventDetail rehydrates the fields.
    }

    public function submitEdit(UpdateEventService $svc): void
    {
        if ($this->detailEventId === null || $this->drawerCreateMode) {
            return;
        }

        $svc->execute(Auth::user(), $this->detailEventId, [
            'title' => $this->formTitle,
            'description' => $this->formDescription !== '' ? $this->formDescription : null,
            'start_at' => $this->normalizeFormDate($this->formStartAt),
            'end_at' => $this->normalizeFormDate($this->formEndAt),
            'is_all_day' => $this->formIsAllDay,
            'color' => $this->formColor,
            'location' => $this->formLocation !== '' ? $this->formLocation : null,
            'recurrence_freq' => $this->formRecurrenceFreq,
            'recurrence_until' => $this->formRecurrenceUntil !== '' ? $this->formRecurrenceUntil : null,
        ]);

        $this->closeModal('calendar-event-detail');
        $this->toast('success', __('Event updated.'));
        $this->dispatch('calendar-events-changed');
    }

    public function deleteEvent(DeleteEventService $svc): void
    {
        if ($this->detailEventId === null) {
            return;
        }

        $svc->execute(Auth::user(), $this->detailEventId);
        $this->closeModal('calendar-event-detail');
        $this->toast('success', __('Event deleted.'));
        $this->dispatch('calendar-events-changed');
    }

    public function openTaskDetail(int $taskId): void
    {
        // Thin passthrough — the <livewire:tasks.task-detail-drawer /> child
        // listens for this event and owns the rest of the flow.
        $this->dispatch('task-detail:open', taskId: $taskId);
    }

    private function resetForm(): void
    {
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formStartAt = '';
        $this->formEndAt = '';
        $this->formIsAllDay = false;
        $this->formColor = EventColor::Danube->value;
        $this->formLocation = '';
        $this->formRecurrenceFreq = RecurrenceFreq::None->value;
        $this->formRecurrenceUntil = '';
        $this->resetErrorBag();
    }

    /**
     * Browser <input type="datetime-local"> emits `Y-m-d\TH:i` (no seconds).
     * Normalize to `Y-m-d H:i:s` so the date validator and DB cast both like it.
     */
    private function normalizeFormDate(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        return CarbonImmutable::parse($value)->format('Y-m-d H:i:s');
    }

    #[On('calendar-events-changed')]
    public function refresh(): void
    {
        // Empty — listener triggers re-render so computeds re-evaluate.
    }

    #[On('task-list-changed')]
    public function refreshOverlay(): void
    {
        // Empty — re-render reloads scheduledTasks() so the calendar overlay
        // reflects task edits/deletes from the drawer.
    }

    public function render()
    {
        return view('livewire.calendar');
    }
}
