<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Ai\Services\AiSystemPromptBuilder;
use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 4: stored memories appear in the system prompt's USER MEMORIES
 * block so future chat sessions stay informed without any prompting from
 * the user.
 */
class MemoryInSystemPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_memories_appear_in_system_prompt(): void
    {
        $user = User::factory()->create();

        UserPreference::create([
            'user_id' => $user->id,
            'key' => 'memory.alpha',
            'value' => ['fact' => 'Prefers kanban view.', 'created_at' => now()->toIso8601String()],
        ]);
        UserPreference::create([
            'user_id' => $user->id,
            'key' => 'memory.beta',
            'value' => ['fact' => 'Default project is Marketing Relaunch.', 'created_at' => now()->toIso8601String()],
        ]);

        // Should NOT appear — non-memory key.
        UserPreference::create([
            'user_id' => $user->id,
            'key' => 'language.task_writing',
            'value' => 'de',
        ]);

        $prompt = app(AiSystemPromptBuilder::class)->build($user);

        $this->assertStringContainsString('USER MEMORIES:', $prompt);
        $this->assertStringContainsString('- Prefers kanban view.', $prompt);
        $this->assertStringContainsString('- Default project is Marketing Relaunch.', $prompt);
        $this->assertStringNotContainsString('language.task_writing', $prompt);
    }

    public function test_no_memories_means_no_memories_block(): void
    {
        $user = User::factory()->create();

        $prompt = app(AiSystemPromptBuilder::class)->build($user);

        $this->assertStringNotContainsString('USER MEMORIES:', $prompt);
    }
}
