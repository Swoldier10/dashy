<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\DeleteProjectStatusAction;
use App\Domains\Projects\Actions\FindProjectStatusAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class DeleteProjectStatusService
{
    public function __construct(
        private FindProjectStatusAction $find,
        private DeleteProjectStatusAction $delete,
    ) {}

    public function execute(User $actor, int $statusId): void
    {
        $status = $this->find->execute($statusId);

        Gate::forUser($actor)->authorize('manageStatuses', $status->project);

        if ($status->tasks()->exists()) {
            throw ValidationException::withMessages([
                'status' => __('You can\'t delete a status while tasks reference it.'),
            ]);
        }

        DB::transaction(fn () => $this->delete->execute($status));
    }
}
