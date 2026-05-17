<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\DeleteProjectAction;
use App\Domains\Projects\Actions\FindProjectAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class DeleteProjectService
{
    public function __construct(
        private FindProjectAction $find,
        private DeleteProjectAction $delete,
    ) {}

    public function execute(User $actor, int $projectId): void
    {
        $project = $this->find->execute($projectId);

        Gate::forUser($actor)->authorize('delete', $project);

        $logo = $project->logo;

        DB::transaction(fn () => $this->delete->execute($project));

        $this->deleteIfLocal($logo);
    }

    private function deleteIfLocal(?string $url): void
    {
        if ($url === null) {
            return;
        }

        $publicPrefix = Storage::disk('public')->url('');

        if (! str_starts_with($url, $publicPrefix)) {
            return;
        }

        $relative = ltrim(substr($url, strlen($publicPrefix)), '/');

        Storage::disk('public')->delete($relative);
    }
}
