<?php

namespace App\Domains\Preferences\Services;

use App\Domains\Preferences\Actions\UpsertUserPreferenceAction;
use Illuminate\Support\Facades\DB;

/**
 * Generic public write for a single user preference value. Callers own any
 * domain-specific validation; this service only persists.
 */
final class SetUserPreferenceService
{
    public function __construct(
        private UpsertUserPreferenceAction $upsert,
    ) {}

    public function execute(int $userId, string $key, mixed $value): void
    {
        DB::transaction(fn () => $this->upsert->execute($userId, $key, $value));
    }
}
