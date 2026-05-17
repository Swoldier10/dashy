<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\ListProjectsForUserAction;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListProjectsForUserService
{
    public function __construct(private readonly ListProjectsForUserAction $action) {}

    /**
     * @return Collection<int, Project>
     */
    public function execute(User $user): Collection
    {
        return $this->action->execute($user);
    }
}
