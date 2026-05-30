<?php

namespace App\Domains\Chat\Ai\Contracts;

use App\Models\User;

/**
 * Implemented by tools that render a bespoke preview/confirmation card. The
 * AiToolCardPresenter delegates to the tool when it implements this, so each
 * tool owns its own card shape instead of a central god-method. Tools that
 * use a shared presentation strategy (auto-read / compact-write / bulk /
 * destructive) do NOT implement this — the presenter handles those groups.
 */
interface PresentsToolCard
{
    /**
     * @param  array<string, mixed>  $toolCall  the stored tool_call payload
     * @return array<string, mixed> the enriched view passed to the card blade
     */
    public function presentCard(array $toolCall, User $user): array;
}
