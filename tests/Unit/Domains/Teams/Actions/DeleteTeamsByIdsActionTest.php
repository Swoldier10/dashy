<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\DeleteTeamsByIdsAction;
use App\Domains\Teams\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTeamsByIdsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_only_the_given_teams(): void
    {
        $a = Team::factory()->create();
        $b = Team::factory()->create();
        $keep = Team::factory()->create();

        (new DeleteTeamsByIdsAction)->execute([(int) $a->id, (int) $b->id]);

        $this->assertDatabaseMissing('teams', ['id' => $a->id]);
        $this->assertDatabaseMissing('teams', ['id' => $b->id]);
        $this->assertDatabaseHas('teams', ['id' => $keep->id]);
    }

    public function test_empty_list_is_a_no_op(): void
    {
        Team::factory()->create();

        (new DeleteTeamsByIdsAction)->execute([]);

        $this->assertSame(1, Team::count());
    }
}
