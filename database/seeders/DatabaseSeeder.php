<?php

namespace Database\Seeders;

use App\Domains\Teams\Services\EnsurePersonalTeamService;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Idempotent and team-safe: every seeded user gets a personal team via
     * EnsurePersonalTeamService, so seeding never produces the teamless users
     * the bare factory would (every real user must own a personal team).
     */
    public function run(): void
    {
        $ensurePersonalTeam = app(EnsurePersonalTeamService::class);

        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User'],
        );

        $ensurePersonalTeam->execute($testUser);
    }
}
