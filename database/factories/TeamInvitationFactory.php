<?php

namespace Database\Factories;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TeamInvitation>
 */
class TeamInvitationFactory extends Factory
{
    protected $model = TeamInvitation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now();

        return [
            'team_id' => Team::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => TeamRole::Member,
            'token_hash' => hash('sha256', Str::random(64)),
            'expires_at' => $now->copy()->addDays(7),
            'accepted_at' => null,
            'revoked_at' => null,
            'invited_by_user_id' => User::factory(),
            'accepted_by_user_id' => null,
            'last_sent_at' => $now,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => now(),
            'accepted_by_user_id' => User::factory(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function asOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => TeamRole::Owner,
        ]);
    }
}
