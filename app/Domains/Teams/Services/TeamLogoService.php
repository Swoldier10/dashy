<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\UpdateTeamAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class TeamLogoService
{
    public function __construct(
        private UpdateTeamAction $updateTeam,
    ) {}

    public function upload(User $actor, Team $team, UploadedFile $file): Team
    {
        Gate::forUser($actor)->authorize('update', $team);

        Validator::make(['logo' => $file], [
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ])->validate();

        $oldUrl = $team->logo;
        $newPath = $file->storePublicly("team-logos/{$team->id}", 'public');
        $newUrl = Storage::disk('public')->url($newPath);

        try {
            $updated = DB::transaction(fn () => $this->updateTeam->execute($team, ['logo' => $newUrl]));
        } catch (Throwable $e) {
            Storage::disk('public')->delete($newPath);
            throw $e;
        }

        $this->deleteIfLocal($oldUrl);

        return $updated;
    }

    public function remove(User $actor, Team $team): Team
    {
        Gate::forUser($actor)->authorize('update', $team);

        $this->deleteIfLocal($team->logo);

        return DB::transaction(fn () => $this->updateTeam->execute($team, ['logo' => null]));
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
