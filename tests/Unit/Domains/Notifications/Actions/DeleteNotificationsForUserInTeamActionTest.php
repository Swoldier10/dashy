<?php

namespace Tests\Unit\Domains\Notifications\Actions;

use App\Domains\Notifications\Actions\DeleteNotificationsForUserInTeamAction;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteNotificationsForUserInTeamActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_the_users_notifications_for_the_team_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $team = Team::factory()->create();
        $otherTeam = Team::factory()->create();

        Notification::factory()->count(2)->for($user, 'recipient')->forTeam($team)->create();
        $keptOtherTeam = Notification::factory()->for($user, 'recipient')->forTeam($otherTeam)->create();
        $keptOtherUser = Notification::factory()->for($other, 'recipient')->forTeam($team)->create();

        $deleted = (new DeleteNotificationsForUserInTeamAction)->execute($user->id, $team->id);

        $this->assertSame(2, $deleted);
        $this->assertNotNull($keptOtherTeam->fresh());
        $this->assertNotNull($keptOtherUser->fresh());
    }

    public function test_preserves_the_removed_from_team_notice(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $removalNotice = Notification::factory()
            ->for($user, 'recipient')
            ->forTeam($team)
            ->ofType(NotificationType::RemovedFromTeam)
            ->create();
        Notification::factory()->for($user, 'recipient')->forTeam($team)->create();

        $deleted = (new DeleteNotificationsForUserInTeamAction)->execute($user->id, $team->id);

        $this->assertSame(1, $deleted);
        $this->assertNotNull($removalNotice->fresh());
    }
}
