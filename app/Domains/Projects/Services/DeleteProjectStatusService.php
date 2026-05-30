<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\DeleteProjectStatusAction;
use App\Domains\Projects\Actions\FindProjectStatusAction;
use App\Domains\Projects\Actions\StatusHasTasksAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class DeleteProjectStatusService
{
    public function __construct(
        private FindProjectStatusAction $find,
        private DeleteProjectStatusAction $delete,
        private StatusHasTasksAction $hasTasks,
    ) {}

    public function execute(User $actor, int $statusId): void
    {
        $status = $this->find->execute($statusId);

        Gate::forUser($actor)->authorize('manageStatuses', $status->project);

        if ($this->hasTasks->execute($status)) {
            throw ValidationException::withMessages([
                'status' => __('You can\'t delete a status while tasks reference it.'),
            ]);
        }

        DB::transaction(fn () => $this->delete->execute($status));
    }
}
