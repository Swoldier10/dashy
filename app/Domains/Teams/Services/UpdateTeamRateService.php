<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\UpdateTeamAction;
use App\Domains\Teams\Enums\Currency;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UpdateTeamRateService
{
    public function __construct(
        private UpdateTeamAction $updateTeam,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, Team $team, array $input): Team
    {
        Gate::forUser($actor)->authorize('update', $team);

        $normalized = [
            'hourly_rate' => $this->normalize($input['hourly_rate'] ?? null),
            'currency' => $this->normalize($input['currency'] ?? null),
        ];

        $validated = Validator::make($normalized, [
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'required_with:currency'],
            'currency' => ['nullable', 'string', Rule::enum(Currency::class), 'required_with:hourly_rate'],
        ])->validate();

        return DB::transaction(fn () => $this->updateTeam->execute($team, [
            'hourly_rate' => $validated['hourly_rate'],
            'currency' => $validated['currency'],
        ]));
    }

    private function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
