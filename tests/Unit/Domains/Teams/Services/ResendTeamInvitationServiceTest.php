<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Exceptions\InvalidInvitationException;
use App\Domains\Teams\Exceptions\InvitationResendThrottledException;
use App\Domains\Teams\Mail\TeamInvitationMail;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Domains\Teams\Services\ResendTeamInvitationService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResendTeamInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_resend_after_throttle_window(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'last_sent_at' => now()->subHours(2),
            'token_hash' => str_repeat('a', 64),
        ]);

        $result = app(ResendTeamInvitationService::class)->execute($owner, $invitation->id);

        $this->assertNotSame(str_repeat('a', 64), $result->token_hash);
        $this->assertTrue($result->last_sent_at->isAfter(now()->subMinute()));
        $this->assertTrue($result->expires_at->isAfter(now()->addDays(6)));
        Mail::assertSent(TeamInvitationMail::class);
    }

    public function test_resend_within_window_throttled(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'last_sent_at' => now()->subMinutes(15),
        ]);

        $this->expectException(InvitationResendThrottledException::class);

        try {
            app(ResendTeamInvitationService::class)->execute($owner, $invitation->id);
        } finally {
            Mail::assertNothingSent();
        }
    }

    public function test_non_owner_forbidden(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'last_sent_at' => now()->subHours(2),
        ]);

        $this->expectException(AuthorizationException::class);

        app(ResendTeamInvitationService::class)->execute($member, $invitation->id);
    }

    public function test_missing_invitation_throws(): void
    {
        [$owner] = $this->makeTeamWithOwner();

        $this->expectException(InvalidInvitationException::class);

        app(ResendTeamInvitationService::class)->execute($owner, 999999);
    }

    public function test_non_pending_invitation_rejected(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        $accepted = TeamInvitation::factory()->accepted()->create([
            'team_id' => $team->id,
            'last_sent_at' => now()->subHours(2),
        ]);

        $this->expectException(InvalidInvitationException::class);

        app(ResendTeamInvitationService::class)->execute($owner, $accepted->id);
    }

    /** @return array{0: User, 1: Team} */
    private function makeTeamWithOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }
}
