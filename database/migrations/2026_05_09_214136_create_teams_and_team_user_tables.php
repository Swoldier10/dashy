<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->boolean('personal_team')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16);
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
            $table->index(['user_id', 'team_id']);
        });

        $this->backfillPersonalTeams();
    }

    public function down(): void
    {
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }

    /**
     * Idempotent backfill: every existing user gets a personal team they
     * own, unless they already have one. Re-running this migration is safe.
     */
    private function backfillPersonalTeams(): void
    {
        DB::transaction(function () {
            $now = now();

            DB::table('users')->orderBy('id')->each(function ($row) use ($now) {
                $alreadyHasPersonalTeam = DB::table('team_user')
                    ->join('teams', 'teams.id', '=', 'team_user.team_id')
                    ->where('team_user.user_id', $row->id)
                    ->where('teams.personal_team', true)
                    ->exists();

                if ($alreadyHasPersonalTeam) {
                    return;
                }

                $teamId = DB::table('teams')->insertGetId([
                    'name' => $this->personalTeamNameFor($row),
                    'personal_team' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('team_user')->insert([
                    'team_id' => $teamId,
                    'user_id' => $row->id,
                    'role' => 'owner',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        });
    }

    private function personalTeamNameFor(object $row): string
    {
        $first = isset($row->first_name) ? trim((string) $row->first_name) : '';

        if ($first !== '') {
            return $first."'s Team";
        }

        $name = isset($row->name) ? trim((string) $row->name) : '';
        if ($name !== '') {
            return $name."'s Team";
        }

        return 'Personal';
    }
};
