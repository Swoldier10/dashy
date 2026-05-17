<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ChatPanelTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCodex(): User
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);

        return $user;
    }

    public function test_renders_for_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('chat.chat-panel')->assertOk();
    }

    public function test_shows_connect_banner_when_codex_not_connected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('chat.chat-panel')
            ->assertSee('Connect Codex to start chatting');
    }

    public function test_does_not_show_banner_when_connected(): void
    {
        $this->actingAs($this->userWithCodex());

        Livewire::test('chat.chat-panel')
            ->assertDontSee('Connect Codex to start chatting');
    }

    public function test_send_message_creates_chat_and_persists_assistant_reply(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('Hello');
            yield ChatStreamEvent::textDelta(' world');
        });
        $this->app->instance(CodexClient::class, $mock);

        // Phase 1: persists the user message and dispatches the streaming kickoff.
        // Phase 2: pulls the assistant reply and persists it.
        Livewire::test('chat.chat-panel')
            ->set('message', 'How are you?')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertDispatched('process-assistant-reply')
            ->call('processAssistantReply');

        $chat = Chat::firstOrFail();
        $this->assertSame($user->id, $chat->user_id);
        $this->assertSame('How are you?', $chat->title);
        $messages = $chat->messages;
        $this->assertCount(2, $messages);
        $this->assertSame('user', $messages[0]->role->value);
        $this->assertSame('How are you?', $messages[0]->content);
        $this->assertSame('assistant', $messages[1]->role->value);
        $this->assertSame('Hello world', $messages[1]->content);
    }

    public function test_send_message_phase_one_persists_only_the_user_message_and_does_not_call_codex(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldNotReceive('streamChat');
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'just persist me')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSet('message', '')
            ->assertSet('isThinking', true)
            ->assertDispatched('chat-list-changed')
            ->assertDispatched('process-assistant-reply');

        $this->assertSame(1, Chat::count());
        $this->assertSame(1, Message::count(), 'Only the user message is persisted in phase 1.');
        $this->assertSame('user', Message::first()->role->value);
    }

    public function test_send_message_empty_is_silent_no_op(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        Livewire::test('chat.chat-panel')
            ->set('message', '   ')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertNotDispatched('process-assistant-reply');

        $this->assertSame(0, Chat::count());
        $this->assertSame(0, Message::count());
    }

    public function test_send_message_clears_stale_error_on_next_successful_send(): void
    {
        // Reproduces the user-reported bug: an error left in the bag by a
        // previous failed send (e.g. when Codex was not connected) used to
        // persist after a subsequent successful send. resetErrorBag at the
        // top of sendMessage clears it.
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test('chat.chat-panel');

        // First send fails because Codex is not connected — adds "Connect Codex" error.
        $component->set('message', 'first try')
            ->call('sendMessage')
            ->assertHasErrors('message');

        // Now connect Codex and stub the stream.
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('ok');
        });
        $this->app->instance(CodexClient::class, $mock);

        // Second send should clear the stale error and proceed to phase 1 success.
        $component->set('message', 'second try')
            ->call('sendMessage')
            ->assertHasNoErrors('message')
            ->assertDispatched('process-assistant-reply');
    }

    public function test_send_message_blocked_when_not_connected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('chat.chat-panel')
            ->set('message', 'hi')
            ->call('sendMessage')
            ->assertHasErrors('message');

        $this->assertSame(0, Chat::count());
    }

    public function test_mount_with_owned_chat_id_hydrates_active_chat(): void
    {
        // After the sidebar redesign, chat selection happens via the URL
        // (route param /chat/{chat}). The panel reads it on mount.
        $user = User::factory()->create();
        $this->actingAs($user);
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'A']);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->assertSet('activeChatId', $chat->id);
    }

    public function test_mount_with_other_users_chat_id_leaves_active_null(): void
    {
        // Defence in depth: a malformed URL pointing at someone else's chat
        // shouldn't hydrate it. FindChatForUserAction returns null for
        // non-owned chats and mount() ignores that.
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $chat = Chat::create(['user_id' => $owner->id, 'title' => 'Theirs']);
        $this->actingAs($intruder);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->assertSet('activeChatId', null);
    }

    public function test_available_projects_lists_only_projects_in_user_teams(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $userTeam = Team::factory()->create();
        $userTeam->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $ownedProject = Project::factory()->create(['team_id' => $userTeam->id, 'name' => 'Aurora']);

        // A project on a team the user does NOT belong to must be excluded.
        $strangerTeam = Team::factory()->create();
        Project::factory()->create(['team_id' => $strangerTeam->id, 'name' => 'Hidden']);

        // The project picker only renders in the active-chat composer (the
        // chat-home empty state matches the mockup which has no picker), so
        // we need a chat to assert the visible name.
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Existing']);

        Livewire::test('chat.chat-panel')
            ->set('activeChatId', $chat->id)
            ->assertSee('Aurora')
            ->assertDontSee('Hidden');

        $projects = Livewire::test('chat.chat-panel')->instance()->availableProjects;
        $this->assertCount(1, $projects);
        $this->assertSame($ownedProject->id, $projects->first()->id);
    }

    public function test_available_projects_empty_when_user_has_no_teams(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $projects = Livewire::test('chat.chat-panel')->instance()->availableProjects;

        $this->assertCount(0, $projects);
    }

    public function test_project_quick_select_hidden_when_no_projects(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Existing']);

        Livewire::test('chat.chat-panel')
            ->set('activeChatId', $chat->id)
            ->assertDontSeeHtml('data-test="composer-project-trigger"');
    }

    public function test_project_quick_select_visible_in_active_chat_when_projects_exist(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        Project::factory()->create(['team_id' => $team->id, 'name' => 'Aurora']);

        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Existing']);

        Livewire::test('chat.chat-panel')
            ->set('activeChatId', $chat->id)
            ->assertSeeHtml('data-test="composer-project-trigger"');
    }

    public function test_project_quick_select_hidden_on_chat_home_empty_state(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        Project::factory()->create(['team_id' => $team->id, 'name' => 'Aurora']);

        // The redesigned chat-home empty state mirrors the mockup, which has
        // no project picker below the composer. The picker is reserved for
        // the active-chat pinned composer.
        Livewire::test('chat.chat-panel')
            ->assertDontSeeHtml('data-test="composer-project-trigger"');
    }

    public function test_send_message_dispatches_composer_reset_so_editor_clears_on_client(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('ok');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'hi')
            ->call('sendMessage')
            ->assertDispatched('composer-reset');
    }

    public function test_send_message_in_existing_chat_only_persists_user_message_not_new_chat(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Existing']);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('reply');
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('activeChatId', $chat->id)
            ->set('message', 'follow-up')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $this->assertSame(1, Chat::count(), 'No new chat should have been created.');
        $this->assertSame(2, Message::count(), 'User + assistant message persisted.');
    }
}
