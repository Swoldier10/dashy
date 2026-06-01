<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Exceptions\CodexApiException;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Livewire\Livewire;
use Mockery;
use RuntimeException;
use Tests\TestCase;

/**
 * Every Codex/connection failure must degrade to a friendly toast (or the
 * reconnect prompt) and reset the thinking state — never a raw HTTP 500.
 */
class ChatErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    private function connectedUser(): User
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        return $user;
    }

    private function mockClientThrowing(callable $generator): void
    {
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing($generator);
        $this->app->instance(CodexClient::class, $mock);
    }

    public function test_out_of_credits_saves_partial_and_toasts_without_500(): void
    {
        $this->actingAs($this->connectedUser());

        $this->mockClientThrowing(function () {
            yield ChatStreamEvent::textDelta('Here is a partial answer');
            throw new CodexApiException('429 quota', status: 429, errorType: 'insufficient_quota');
        });

        Livewire::test('chat.chat-panel')
            ->set('message', 'hi')
            ->call('sendMessage')
            ->call('processAssistantReply')
            ->assertSet('isThinking', false)
            ->assertDispatched('dashy-toast');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertStringContainsString('partial answer', $assistant->content);
        $this->assertStringContainsString('stream interrupted', $assistant->content);
    }

    public function test_connection_failure_is_handled_without_500(): void
    {
        $this->actingAs($this->connectedUser());

        $this->mockClientThrowing(function () {
            throw CodexApiException::connectionFailed(new ConnectionException('Connection refused'));
            yield; // make the closure a generator
        });

        Livewire::test('chat.chat-panel')
            ->set('message', 'hi')
            ->call('sendMessage')
            ->call('processAssistantReply')
            ->assertSet('isThinking', false)
            ->assertDispatched('dashy-toast');
    }

    public function test_revoked_session_prompts_reconnect_without_500(): void
    {
        $this->actingAs($this->connectedUser());

        $this->mockClientThrowing(function () {
            throw new CodexNotConnectedException('session gone');
            yield;
        });

        Livewire::test('chat.chat-panel')
            ->set('message', 'hi')
            ->call('sendMessage')
            ->call('processAssistantReply')
            ->assertSet('isThinking', false)
            ->assertHasErrors('message');
    }

    public function test_unexpected_throwable_degrades_to_toast_without_500(): void
    {
        $this->actingAs($this->connectedUser());

        $this->mockClientThrowing(function () {
            throw new RuntimeException('boom');
            yield;
        });

        Livewire::test('chat.chat-panel')
            ->set('message', 'hi')
            ->call('sendMessage')
            ->call('processAssistantReply')
            ->assertSet('isThinking', false)
            ->assertDispatched('dashy-toast');
    }

    public function test_concurrent_send_is_ignored_while_a_turn_is_in_flight(): void
    {
        $this->actingAs($this->connectedUser());

        Livewire::test('chat.chat-panel')
            ->set('isThinking', true)
            ->set('message', 'hi')
            ->call('sendMessage');

        $this->assertSame(0, Message::count(), 'A send while a turn is in flight must be ignored.');
    }
}
