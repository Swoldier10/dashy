<?php

namespace App\Domains\Chat\Ai\Services;

use App\Domains\Auth\Services\FindUsersByIdsService;
use App\Domains\Calendar\Services\FindEventService;
use App\Domains\Projects\Services\FindProjectService;
use App\Domains\Projects\Services\FindProjectStatusService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

/**
 * Resolves short, quoted human labels ("task name", "status", …) for tool
 * cards, scoped to the acting user. Extracted from AiToolCardPresenter so the
 * cross-domain Find services live in one focused place and both the presenter
 * strategies and the per-tool cards can reuse them. Every lookup degrades to
 * "#id" if the record is gone or inaccessible — cards never break.
 */
final class ToolCardLabelResolver
{
    public function __construct(
        private FindProjectService $findProject,
        private FindProjectStatusService $findStatus,
        private FindTaskService $findTask,
        private FindUsersByIdsService $findUsers,
        private FindEventService $findEvent,
        private FindTeamForUserService $findTeam,
    ) {}

    public function taskLabel(User $user, int $id): string
    {
        try {
            return '"'.Str::limit((string) $this->findTask->execute($user, $id)->name, 40).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }

    public function statusLabel(User $user, int $id): string
    {
        try {
            return '"'.Str::limit((string) $this->findStatus->execute($user, $id)->name, 30).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }

    public function projectLabel(User $user, int $id): string
    {
        try {
            return '"'.Str::limit((string) $this->findProject->execute($user, $id)->name, 40).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }

    public function userLabel(int $id): string
    {
        $found = $this->findUsers->execute([$id])->first();

        return $found !== null ? (string) $found->name : '#'.$id;
    }

    public function eventLabel(User $user, int $id): string
    {
        try {
            return '"'.Str::limit((string) $this->findEvent->execute($user, $id)->title, 40).'"';
        } catch (Throwable) {
            return '#'.$id;
        }
    }

    public function teamLabel(User $user, int $id): string
    {
        $team = $this->findTeam->execute($user, $id);

        return $team !== null ? '"'.Str::limit((string) $team->name, 40).'"' : '#'.$id;
    }
}
