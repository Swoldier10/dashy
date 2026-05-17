<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ChatCreateProjectEditTest extends TestCase
{
    use RefreshDatabase;

    private function userInTwoTeams(): array
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $alpha = Team::factory()->create(['name' => 'Alpha']);
        $beta = Team::factory()->create(['name' => 'Beta']);
        $alpha->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $beta->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        return [$user, $alpha, $beta];
    }

    private function pendingCreateProject(User $user, Team $team, ?array $logoAttachment = null): array
    {
        $chat = Chat::create(['user_id' => $user->id]);
        $assistant = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_proj',
                'name' => 'create_project',
                'arguments' => array_filter([
                    'team_id' => $team->id,
                    'name' => 'LLM Suggested',
                    'description' => null,
                    'logo_attachment' => $logoAttachment,
                ], fn ($v) => $v !== null || $logoAttachment !== null),
                'status' => 'pending',
            ],
        ]);

        return [$chat, $assistant];
    }

    public function test_edits_to_name_and_team_persist_when_confirmed(): void
    {
        [$user, $alpha, $beta] = $this->userInTwoTeams();
        $this->actingAs($user);
        [$chat, $assistant] = $this->pendingCreateProject($user, $alpha);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('toolCallEdits.'.$assistant->id.'.name', 'Edited Name')
            ->set('toolCallEdits.'.$assistant->id.'.team_id', $beta->id)
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(1, Project::count());
        $project = Project::first();
        $this->assertSame('Edited Name', $project->name);
        $this->assertSame($beta->id, $project->team_id);
    }

    public function test_invalid_edit_keeps_card_pending_with_validation_errors(): void
    {
        [$user, $alpha] = $this->userInTwoTeams();
        $this->actingAs($user);
        [$chat, $assistant] = $this->pendingCreateProject($user, $alpha);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('toolCallEdits.'.$assistant->id.'.name', '   ')
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(0, Project::count());
        $assistant->refresh();
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertNotEmpty($assistant->tool_call['validation_errors']);
    }

    public function test_replacing_logo_via_upload_persists_new_logo(): void
    {
        Storage::fake('public');
        [$user, $alpha] = $this->userInTwoTeams();
        $this->actingAs($user);
        [$chat, $assistant] = $this->pendingCreateProject($user, $alpha);

        $newImage = UploadedFile::fake()->image('replacement.png');

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('toolCallLogoUploads.'.$assistant->id, $newImage)
            ->call('confirmToolCall', $assistant->id);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertNotNull($project->logo);
        $this->assertStringContainsString("project-logos/{$alpha->id}/", $project->logo);
    }

    public function test_clearing_logo_persists_null_logo_even_when_chat_still_has_image(): void
    {
        Storage::fake('public');
        [$user, $alpha] = $this->userInTwoTeams();
        $this->actingAs($user);

        // The LLM-emitted args carry a snapshotted logo. The user clicks
        // "Remove" — the chat may still have the image, but the project
        // must end up with no logo.
        $source = UploadedFile::fake()->image('snapshot.png');
        $sourcePath = $source->storePublicly('chat-attachments/'.$user->id.'/_pending', 'public');

        [$chat, $assistant] = $this->pendingCreateProject($user, $alpha, [
            'path' => $sourcePath,
            'url' => Storage::disk('public')->url($sourcePath),
            'mime' => 'image/png',
            'name' => 'snapshot.png',
        ]);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('clearToolCallLogo', $assistant->id)
            ->call('confirmToolCall', $assistant->id);

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertNull($project->logo);
    }
}
