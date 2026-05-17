<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Actions\UpdateProjectAction;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class UpdateProjectService
{
    public function __construct(
        private FindProjectAction $find,
        private UpdateProjectAction $update,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, int $projectId, array $input, ?UploadedFile $logo = null): Project
    {
        $project = $this->find->execute($projectId);

        Gate::forUser($actor)->authorize('update', $project);

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        if ($logo !== null) {
            Validator::make(['logo' => $logo], [
                'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ])->validate();
        }

        $oldLogoUrl = $project->logo;
        $newLogoPath = null;
        $newLogoUrl = null;

        if ($logo !== null) {
            $newLogoPath = $logo->storePublicly("project-logos/{$project->team_id}", 'public');
            $newLogoUrl = Storage::disk('public')->url($newLogoPath);
        }

        try {
            $updated = DB::transaction(function () use ($project, $validated, $logo, $newLogoUrl) {
                $attributes = [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                ];
                if ($logo !== null) {
                    $attributes['logo'] = $newLogoUrl;
                }

                return $this->update->execute($project, $attributes);
            });
        } catch (Throwable $e) {
            if ($newLogoPath !== null) {
                Storage::disk('public')->delete($newLogoPath);
            }
            throw $e;
        }

        if ($logo !== null) {
            $this->deleteIfLocal($oldLogoUrl);
        }

        return $updated;
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
