<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\DTOs\VisitorInvitationView;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Domains\Teams\Services\ResolveInvitationForVisitorService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveInvitationForVisitorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_token_returns_invalid_status(): void
    {
        $view = app(ResolveInvitationForVisitorService::class)->execute('no-such-token', null);

        $this->assertSame(VisitorInvitationView::STATUS_INVALID, $view->status);
    }

    public function test_revoked_returns_revoked_status(): void
    {
        $token = 'tk-rv';
        TeamInvitation::factory()->revoked()->create(['token_hash' => hash('sha256', $token)]);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, null);

        $this->assertSame(VisitorInvitationView::STATUS_REVOKED, $view->status);
    }

    public function test_expired_returns_expired_status(): void
    {
        $token = 'tk-ex';
        TeamInvitation::factory()->expired()->create(['token_hash' => hash('sha256', $token)]);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, null);

        $this->assertSame(VisitorInvitationView::STATUS_EXPIRED, $view->status);
    }

    public function test_accepted_by_other_returns_accepted_by_other(): void
    {
        $other = User::factory()->create();
        $token = 'tk-ao';
        TeamInvitation::factory()->create([
            'token_hash' => hash('sha256', $token),
            'accepted_at' => now(),
            'accepted_by_user_id' => $other->id,
        ]);

        $current = User::factory()->create();

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, $current);

        $this->assertSame(VisitorInvitationView::STATUS_ACCEPTED_BY_OTHER, $view->status);
    }

    public function test_already_member_when_accepted_by_current_user(): void
    {
        $user = User::factory()->create();
        $token = 'tk-am';
        TeamInvitation::factory()->create([
            'token_hash' => hash('sha256', $token),
            'accepted_at' => now(),
            'accepted_by_user_id' => $user->id,
        ]);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, $user);

        $this->assertSame(VisitorInvitationView::STATUS_ALREADY_MEMBER, $view->status);
    }

    public function test_email_mismatch_when_auth_user_differs(): void
    {
        $token = 'tk-em';
        TeamInvitation::factory()->create([
            'email' => 'bound@example.com',
            'token_hash' => hash('sha256', $token),
        ]);
        $current = User::factory()->create(['email' => 'other@example.com']);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, $current);

        $this->assertSame(VisitorInvitationView::STATUS_EMAIL_MISMATCH, $view->status);
        $this->assertSame('bound@example.com', $view->boundEmail);
    }

    public function test_already_member_via_other_path(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'dual@example.com']);
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $token = 'tk-mm';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'dual@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, $user);

        $this->assertSame(VisitorInvitationView::STATUS_ALREADY_MEMBER, $view->status);
    }

    public function test_ready_to_accept_when_email_matches_authenticated_user(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['email' => 'ready@example.com']);
        $token = 'tk-ra';
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'ready@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, $user);

        $this->assertSame(VisitorInvitationView::STATUS_READY_TO_ACCEPT, $view->status);
    }

    public function test_needs_login_when_guest_and_email_belongs_to_existing_user(): void
    {
        User::factory()->create(['email' => 'has-account@example.com']);
        $token = 'tk-nl';
        TeamInvitation::factory()->create([
            'email' => 'has-account@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, null);

        $this->assertSame(VisitorInvitationView::STATUS_NEEDS_LOGIN, $view->status);
        $this->assertSame('has-account@example.com', $view->boundEmail);
    }

    public function test_needs_register_when_guest_and_no_account_exists(): void
    {
        $token = 'tk-nr';
        TeamInvitation::factory()->create([
            'email' => 'fresh@example.com',
            'token_hash' => hash('sha256', $token),
        ]);

        $view = app(ResolveInvitationForVisitorService::class)->execute($token, null);

        $this->assertSame(VisitorInvitationView::STATUS_NEEDS_REGISTER, $view->status);
        $this->assertSame('fresh@example.com', $view->boundEmail);
    }
}
