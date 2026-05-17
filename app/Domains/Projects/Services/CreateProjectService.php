<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\CreateProjectAction;
use App\Domains\Projects\Actions\CreateProjectStatusAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

final class CreateProjectService
{
    public function __construct(
        private CreateProjectAction $createProject,
        private CreateProjectStatusAction $createStatus,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @param  list<array{category:string,name:string}>  $statuses
     */
    public function execute(
        User $actor,
        Team $team,
        array $input,
        ?UploadedFile $logo = null,
        array $statuses = [],
    ): Project {
        Gate::forUser($actor)->authorize('create', [Project::class, $team]);

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        if ($logo !== null) {
            Validator::make(['logo' => $logo], [
                'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ])->validate();
        }

        if ($statuses !== []) {
            Validator::make(['statuses' => $statuses], [
                'statuses' => ['array'],
                'statuses.*.category' => ['required', Rule::enum(ProjectStatusCategory::class)],
                'statuses.*.name' => ['required', 'string', 'max:60'],
            ])->validate();
        }

        $logoPath = null;
        $logoUrl = null;

        if ($logo !== null) {
            $logoPath = $logo->storePublicly("project-logos/{$team->id}", 'public');
            $logoUrl = Storage::disk('public')->url($logoPath);
        }

        try {
            return DB::transaction(function () use ($team, $validated, $logoUrl, $statuses) {
                $project = $this->createProject->execute([
                    'team_id' => $team->id,
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'logo' => $logoUrl,
                ]);

                $positionByCategory = [];
                foreach ($statuses as $entry) {
                    $cat = $entry['category'];
                    $positionByCategory[$cat] = ($positionByCategory[$cat] ?? -1) + 1;

                    $this->createStatus->execute([
                        'project_id' => $project->id,
                        'category' => $cat,
                        'name' => $entry['name'],
                        'position' => $positionByCategory[$cat],
                    ]);
                }

                return $project;
            });
        } catch (Throwable $e) {
            if ($logoPath !== null) {
                Storage::disk('public')->delete($logoPath);
            }
            throw $e;
        }
    }
}
