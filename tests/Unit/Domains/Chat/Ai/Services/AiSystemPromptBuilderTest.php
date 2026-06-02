<?php

namespace Tests\Unit\Domains\Chat\Ai\Services;

use App\Domains\Chat\Ai\Services\AiSystemPromptBuilder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSystemPromptBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_includes_workspace_context_for_user(): void
    {
        $user = User::factory()->create();

        $prompt = app(AiSystemPromptBuilder::class)->build($user);

        $this->assertStringContainsString('CONTEXT:', $prompt);
        $this->assertStringContainsString('"today"', $prompt);
        $this->assertStringContainsString((string) $user->id, $prompt);
    }

    public function test_prompt_mandates_german_task_text_regardless_of_input_language(): void
    {
        $user = User::factory()->create();

        $prompt = app(AiSystemPromptBuilder::class)->build($user);

        $this->assertStringContainsString('LANGUAGE', $prompt);
        $this->assertStringContainsString('German (de-DE)', $prompt);
        $this->assertStringContainsString('no matter what language the user wrote in', $prompt);
    }

    public function test_prompt_requires_scrum_style_description_with_german_headings(): void
    {
        $user = User::factory()->create();

        $prompt = app(AiSystemPromptBuilder::class)->build($user);

        $this->assertStringContainsString('## Beschreibung', $prompt);
        $this->assertStringContainsString('## Akzeptanzkriterien', $prompt);
    }

    public function test_prompt_constrains_task_name_to_imperative_german_form(): void
    {
        $user = User::factory()->create();

        $prompt = app(AiSystemPromptBuilder::class)->build($user);

        $this->assertStringContainsString('imperative German title', $prompt);
        $this->assertStringContainsString('3–8 words', $prompt);
    }

    public function test_prompt_includes_create_team_and_invite_member_rules(): void
    {
        $user = User::factory()->create();

        $prompt = app(AiSystemPromptBuilder::class)->build($user);

        $this->assertStringContainsString('Rules for create_team:', $prompt);
        $this->assertStringContainsString('do NOT translate it to German', $prompt);
        $this->assertStringContainsString('Rules for invite_team_member:', $prompt);
        $this->assertStringContainsString('NEVER invent, guess', $prompt);
    }
}
