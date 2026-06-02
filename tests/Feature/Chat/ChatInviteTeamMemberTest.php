<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Mail\TeamInvitationMail;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ChatInviteTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Team} */
    private function userWithCodexInTeam(string $role = TeamRole::Owner->value): array
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => $role]);

        return [$user, $team];
    }

    private function mockStreamedToolCall(string $callId, string $arguments): void
    {
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () use ($callId, $arguments) {
            yield ChatStreamEvent::toolCallStarted($callId, 'invite_team_member');
            yield ChatStreamEvent::toolCallCompleted($callId, 'invite_team_member', $arguments);
        });
        $this->app->instance(CodexClient::class, $mock);
    }

    public function test_owner_invites_member_via_card_and_mail_is_sent(): void
    {
        Mail::fake();
        [$user, $team] = $this->userWithCodexInTeam();
        $this->actingAs($user);

        $this->mockStreamedToolCall('fc_i1', json_encode([
            'team_id' => $team->id,
            'email' => 'newbie@example.com',
            'role' => 'member',
        ]));

        Livewire::test('chat.chat-panel')
            ->set('message', 'invite newbie@example.com to the team')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertSame(0, TeamInvitation::count(), 'Tool call must NOT auto-execute.');
        Mail::assertNothingSent();

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        $invitation = TeamInvitation::firstOrFail();
        $this->assertSame('newbie@example.com', $invitation->email);
        $this->assertSame($team->id, $invitation->team_id);
        $this->assertSame(TeamRole::Member, $invitation->role);
        Mail::assertSent(TeamInvitationMail::class, 1);

        $assistant->refresh();
        $this->assertSame('created', $assistant->tool_call['status']);
        $this->assertSame($invitation->id, $assistant->tool_call['result']['invitation_id']);
    }

    public function test_inviting_existing_member_fails_at_execute_with_inline_error(): void
    {
        Mail::fake();
        [$user, $team] = $this->userWithCodexInTeam();
        $this->actingAs($user);

        $member = User::factory()->create(['email' => 'already@example.com']);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->mockStreamedToolCall('fc_i2', json_encode([
            'team_id' => $team->id,
            'email' => 'already@example.com',
            'role' => 'member',
        ]));

        Livewire::test('chat.chat-panel')
            ->set('message', 'invite already@example.com')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        // The shape checks pass — membership is a deeper rule checked at execute.
        $this->assertSame('pending', $assistant->tool_call['status']);

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        $assistant->refresh();
        $this->assertSame('failed', $assistant->tool_call['status']);
        $this->assertNotEmpty($assistant->tool_call['validation_errors']);
        $this->assertStringContainsString(
            'already on this team',
            implode(' ', $assistant->tool_call['validation_errors']),
        );
        $this->assertSame(0, TeamInvitation::count());
        Mail::assertNothingSent();
    }

    public function test_non_owner_invite_is_rejected_at_initial_validation(): void
    {
        Mail::fake();
        [$user, $team] = $this->userWithCodexInTeam(TeamRole::Member->value);
        $this->actingAs($user);

        $this->mockStreamedToolCall('fc_i3', json_encode([
            'team_id' => $team->id,
            'email' => 'newbie@example.com',
        ]));

        Livewire::test('chat.chat-panel')
            ->set('message', 'invite newbie@example.com')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertSame('failed', $assistant->tool_call['status']);
        $this->assertStringContainsString(
            'Only the team owner',
            implode(' ', $assistant->tool_call['validation_errors']),
        );
        $this->assertSame(0, TeamInvitation::count());
        Mail::assertNothingSent();
    }

    public function test_invite_to_personal_team_is_rejected_at_initial_validation(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $team = Team::factory()->personal()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        $this->mockStreamedToolCall('fc_i4', json_encode([
            'team_id' => $team->id,
            'email' => 'newbie@example.com',
        ]));

        Livewire::test('chat.chat-panel')
            ->set('message', 'invite newbie@example.com')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertSame('failed', $assistant->tool_call['status']);
        $this->assertStringContainsString(
            'personal team',
            implode(' ', $assistant->tool_call['validation_errors']),
        );
        $this->assertSame(0, TeamInvitation::count());
        Mail::assertNothingSent();
    }
}
