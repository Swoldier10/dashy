<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/**
 * Phase 4: the assistant proposes a `remember` card; user clicks Apply;
 * a UserPreference row lands with the fact prefixed by "memory.".
 */
class RememberToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_remember_persists_user_memory_on_confirm(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $this->actingAs($user);

        $args = json_encode([
            'scope' => 'user',
            'fact' => 'I prefer the kanban view over the list view.',
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallStarted('fc_mem', 'remember');
            yield ChatStreamEvent::toolCallCompleted('fc_mem', 'remember', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'remember that I prefer kanban')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->whereNotNull('tool_call')->firstOrFail();
        $this->assertSame('remember', $assistant->tool_call['name']);
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertSame(0, UserPreference::count(), 'no row before confirmation.');

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(1, UserPreference::count());
        $pref = UserPreference::first();
        $this->assertStringStartsWith('memory.', $pref->key);
        $this->assertSame('I prefer the kanban view over the list view.', $pref->value['fact']);
        $this->assertSame($user->id, $pref->user_id);
    }
}
