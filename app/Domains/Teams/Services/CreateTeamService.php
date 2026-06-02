<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\AttachTeamMemberAction;
use App\Domains\Teams\Actions\CreateTeamAction;
use App\Domains\Teams\Actions\UpdateTeamAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class CreateTeamService
{
    public function __construct(
        private CreateTeamAction $createTeam,
        private AttachTeamMemberAction $attachMember,
        private UpdateTeamAction $updateTeam,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $creator, array $input, ?UploadedFile $logo = null): Team
    {
        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:80'],
        ])->validate();

        if ($logo !== null) {
            Validator::make(['logo' => $logo], [
                'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ])->validate();
        }

        // The logo is stored inside the transaction (unlike CreateProjectService)
        // because the per-team storage path needs the id from the insert. A DB
        // rollback can't un-write the file, so the catch deletes the orphan.
        $storedPath = null;

        try {
            return DB::transaction(function () use ($creator, $validated, $logo, &$storedPath) {
                $team = $this->createTeam->execute([
                    'name' => $validated['name'],
                    'personal_team' => false,
                ]);

                $this->attachMember->execute($team, $creator, TeamRole::Owner);

                if ($logo !== null) {
                    $storedPath = $logo->storePublicly("team-logos/{$team->id}", 'public');
                    $team = $this->updateTeam->execute($team, [
                        'logo' => Storage::disk('public')->url($storedPath),
                    ]);
                }

                return $team;
            });
        } catch (Throwable $e) {
            if ($storedPath !== null) {
                Storage::disk('public')->delete($storedPath);
            }
            throw $e;
        }
    }
}
