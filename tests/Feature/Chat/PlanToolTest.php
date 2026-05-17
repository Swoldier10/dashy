<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/**
 * Phase 1G: the `plan` tool emits a checklist that auto-resolves (does not
 * pause the loop). The loop continues to the next iteration immediately so
 * the assistant can proceed with read/write tools after planning.
 */
class PlanToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_executes_inline_and_does_not_block_the_loop(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $this->actingAs($user);

        $args = json_encode(['steps' => ['Find candidates', 'Move them to Done']]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallStarted('fc_plan', 'plan');
            yield ChatStreamEvent::toolCallCompleted('fc_plan', 'plan', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'do a multi-step thing')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $plan = Message::where('role', 'assistant')->whereNotNull('tool_call')->firstOrFail();
        $this->assertSame('plan', $plan->tool_call['name']);
        $this->assertSame('executed', $plan->tool_call['status'], 'plan tool resolves inline.');
        $this->assertSame(
            ['Find candidates', 'Move them to Done'],
            $plan->tool_call['result']['steps'],
        );
    }

    public function test_plan_rejects_too_many_steps(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $this->actingAs($user);

        $args = json_encode([
            'steps' => array_fill(0, 11, 'too many'),
        ]);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () use ($args) {
            yield ChatStreamEvent::toolCallStarted('fc_plan', 'plan');
            yield ChatStreamEvent::toolCallCompleted('fc_plan', 'plan', $args);
        });
        $this->app->instance(CodexClient::class, $mock);

        Livewire::test('chat.chat-panel')
            ->set('message', 'do a multi-step thing')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $plan = Message::where('role', 'assistant')->whereNotNull('tool_call')->firstOrFail();
        $this->assertSame('failed', $plan->tool_call['status']);
        $this->assertNotEmpty($plan->tool_call['validation_errors']);
    }
}
