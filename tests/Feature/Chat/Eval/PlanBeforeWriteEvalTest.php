<?php

namespace Tests\Feature\Chat\Eval;

use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Eval: when the request takes multiple steps, the assistant emits `plan`
 * first, then proceeds. Plan resolves inline (status=executed) and the
 * loop continues to the next iteration without user interaction.
 */
class PlanBeforeWriteEvalTest extends ChatEvalTestCase
{
    use RefreshDatabase;

    public function test_eval(): void
    {
        $user = User::factory()->create();
        CodexConnection::create(['user_id' => $user->id, 'access_token' => 'a', 'expires_at' => now()->addHour()]);
        $this->actingAs($user);

        $this->fakeCodexStream([
            ChatStreamEvent::toolCallStarted('fc_plan', 'plan'),
            ChatStreamEvent::toolCallCompleted('fc_plan', 'plan', json_encode([
                'steps' => ['Look up the user\'s open tasks', 'Summarise them', 'Suggest a priority order'],
            ])),
        ]);

        $this->runOneTurn('Help me plan my day.');

        $plan = Message::where('role', 'assistant')->whereNotNull('tool_call')->firstOrFail();
        $this->assertSame('plan', $plan->tool_call['name']);
        $this->assertSame('executed', $plan->tool_call['status']);
        $this->assertSame(
            ['Look up the user\'s open tasks', 'Summarise them', 'Suggest a priority order'],
            $plan->tool_call['result']['steps'],
        );
    }
}
