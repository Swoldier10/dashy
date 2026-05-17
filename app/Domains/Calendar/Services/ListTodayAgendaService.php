<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\DTOs\AgendaRow;
use App\Domains\Calendar\DTOs\EventOccurrence;
use App\Domains\Calendar\Enums\AgendaKind;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListUserOpenTasksService;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Read-only coordinator that merges today's calendar occurrences and tasks
 * due on the given day into a single, sorted list of {@see AgendaRow}.
 *
 * Pure presentation composition — no DB access or transactions of its own;
 * both upstream services are read-only and already enforce auth.
 */
final class ListTodayAgendaService
{
    public function __construct(
        private ListEventsForUserInRangeService $listEvents,
        private ListUserOpenTasksService $listTasks,
    ) {}

    /**
     * @return list<AgendaRow>
     */
    public function executeFor(User $actor, CarbonImmutable $day): array
    {
        $from = $day->startOfDay();
        $to = $day->endOfDay();

        $rows = [];

        foreach ($this->listEvents->execute($actor, $from, $to) as $occurrence) {
            $rows[] = $this->buildEventRow($occurrence);
        }

        foreach ($this->listTasks->execute($actor) as $task) {
            $end = $task->end_date;

            if ($end === null) {
                continue;
            }

            $due = CarbonImmutable::instance($end);

            if (! $due->isSameDay($day)) {
                continue;
            }

            $rows[] = $this->buildTaskRow($task, $due);
        }

        usort($rows, fn (AgendaRow $a, AgendaRow $b): int => $a->sortAt->getTimestamp() <=> $b->sortAt->getTimestamp());

        return $rows;
    }

    private function buildEventRow(EventOccurrence $occurrence): AgendaRow
    {
        $start = $occurrence->startAt;

        return new AgendaRow(
            kind: AgendaKind::Event,
            timeLabel: $occurrence->event->is_all_day ? __('ALL DAY') : $start->format('G:i'),
            kindLabel: __('CALENDAR'),
            title: $occurrence->event->title,
            accent: '--state-success',
            href: route('calendar'),
            sortAt: $start,
        );
    }

    private function buildTaskRow(Task $task, CarbonImmutable $due): AgendaRow
    {
        $hasTime = $due->format('His') !== '000000';

        $href = $task->project_id !== null
            ? route('tasks.show', $task->project_id)
            : null;

        return new AgendaRow(
            kind: AgendaKind::Task,
            timeLabel: $hasTime ? __('DUE :time', ['time' => $due->format('gA')]) : __('TASK'),
            kindLabel: __('TASK'),
            title: $task->name,
            accent: '--accent',
            href: $href,
            sortAt: $due,
        );
    }
}
