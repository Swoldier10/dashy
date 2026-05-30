<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\IsProjectStatusInProjectAction;
use Illuminate\Validation\ValidationException;

/**
 * Throws when the given status doesn't belong to the given project. The
 * thrown ValidationException uses the exact key/message expected by every
 * Tasks service that previously inlined this check — do not change.
 */
final class AssertProjectStatusInProjectService
{
    public function __construct(
        private IsProjectStatusInProjectAction $isInProject,
    ) {}

    public function execute(int $statusId, int $projectId): void
    {
        if ($this->isInProject->execute($statusId, $projectId)) {
            return;
        }

        throw ValidationException::withMessages([
            'project_status_id' => __('The selected status does not belong to this project.'),
        ]);
    }
}
