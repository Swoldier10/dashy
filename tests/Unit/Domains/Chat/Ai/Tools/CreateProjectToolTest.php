<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Tools\CreateProjectTool;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateProjectToolTest extends TestCase
{
    use RefreshDatabase;

    private function userInTeam(string $role = TeamRole::Member->value): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => $role]);

        return [$user, $team];
    }

    public function test_validate_rejects_missing_team_id(): void
    {
        $user = User::factory()->create();

        $result = app(CreateProjectTool::class)->validate($user, ['name' => 'X']);

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->errors);
    }

    public function test_validate_rejects_team_user_is_not_member_of(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(); // user NOT attached

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => 'X',
        ]);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('do not have access', implode(' ', $result->errors));
    }

    public function test_validate_rejects_empty_name(): void
    {
        [$user, $team] = $this->userInTeam();

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => '   ',
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_name_longer_than_eighty_chars(): void
    {
        [$user, $team] = $this->userInTeam();

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => str_repeat('a', 81),
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_description_longer_than_two_thousand_chars(): void
    {
        [$user, $team] = $this->userInTeam();

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => 'OK',
            'description' => str_repeat('a', 2001),
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_happy_path_returns_sanitized_args(): void
    {
        [$user, $team] = $this->userInTeam();

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => '  Marketing-Website Relaunch  ',
            'description' => 'Neue Marketing-Seite mit Blog.',
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame($team->id, $result->normalized['team_id']);
        $this->assertSame('Marketing-Website Relaunch', $result->normalized['name']);
        $this->assertSame('Neue Marketing-Seite mit Blog.', $result->normalized['description']);
        $this->assertNull($result->normalized['logo_attachment']);
    }

    public function test_validate_returns_null_logo_when_no_image_attached(): void
    {
        [$user, $team] = $this->userInTeam();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'hello',
            'attachments' => [],
        ]);

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => 'Projekt X',
        ], $chat);

        $this->assertTrue($result->valid);
        $this->assertNull($result->normalized['logo_attachment']);
    }

    public function test_validate_preserves_existing_logo_attachment_during_revalidation(): void
    {
        [$user, $team] = $this->userInTeam();
        $chat = Chat::create(['user_id' => $user->id]);
        // The chat now holds a DIFFERENT image than the snapshot in args.
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'new image',
            'attachments' => [[
                'type' => 'image',
                'path' => 'chat-attachments/x/new.png',
                'url' => 'https://test/new.png',
                'mime' => 'image/png',
                'name' => 'new.png',
            ]],
        ]);

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => 'Designsystem',
            'logo_attachment' => [
                'path' => 'chat-attachments/x/original.png',
                'url' => 'https://test/original.png',
                'mime' => 'image/png',
                'name' => 'original.png',
            ],
        ], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        // Original snapshot preserved, NOT the chat's newer image.
        $this->assertSame('chat-attachments/x/original.png', $result->normalized['logo_attachment']['path']);
        $this->assertSame('original.png', $result->normalized['logo_attachment']['name']);
    }

    public function test_validate_treats_explicit_null_logo_attachment_as_cleared(): void
    {
        [$user, $team] = $this->userInTeam();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'image attached',
            'attachments' => [[
                'type' => 'image',
                'path' => 'chat-attachments/x/img.png',
                'url' => 'https://test/img.png',
                'mime' => 'image/png',
                'name' => 'img.png',
            ]],
        ]);

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => 'Cleared logo',
            'logo_attachment' => null,
        ], $chat);

        $this->assertTrue($result->valid);
        $this->assertNull($result->normalized['logo_attachment']);
    }

    public function test_validate_snapshots_first_image_from_latest_user_message(): void
    {
        [$user, $team] = $this->userInTeam();
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'old',
            'attachments' => [[
                'type' => 'image',
                'path' => 'chat-attachments/x/old.png',
                'url' => 'https://test/old.png',
                'mime' => 'image/png',
                'name' => 'old.png',
            ]],
        ]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'new',
            'attachments' => [
                [
                    'type' => 'image',
                    'path' => 'chat-attachments/x/first.png',
                    'url' => 'https://test/first.png',
                    'mime' => 'image/png',
                    'name' => 'first.png',
                ],
                [
                    'type' => 'image',
                    'path' => 'chat-attachments/x/second.png',
                    'url' => 'https://test/second.png',
                    'mime' => 'image/png',
                    'name' => 'second.png',
                ],
            ],
        ]);

        $result = app(CreateProjectTool::class)->validate($user, [
            'team_id' => $team->id,
            'name' => 'Designsystem',
        ], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('chat-attachments/x/first.png', $result->normalized['logo_attachment']['path']);
        $this->assertSame('first.png', $result->normalized['logo_attachment']['name']);
    }

    public function test_execute_creates_project_with_seeded_default_statuses(): void
    {
        [$user, $team] = $this->userInTeam();

        $tool = app(CreateProjectTool::class);
        $valid = $tool->validate($user, [
            'team_id' => $team->id,
            'name' => 'Marketing-Website Relaunch',
            'description' => 'Neue Seite.',
        ]);
        $this->assertTrue($valid->valid);

        $result = $tool->execute($user, $valid->normalized);

        $project = Project::findOrFail($result['project_id']);
        $this->assertSame('Marketing-Website Relaunch', $project->name);
        $this->assertSame('Neue Seite.', $project->description);
        $this->assertSame($team->id, $project->team_id);

        $statuses = ProjectStatus::query()
            ->where('project_id', $project->id)
            ->get();
        $this->assertCount(3, $statuses);

        $byCategory = $statuses->mapWithKeys(fn (ProjectStatus $s) => [$s->category->value => $s->name])->all();
        $this->assertSame('Zu erledigen', $byCategory[ProjectStatusCategory::NotStarted->value]);
        $this->assertSame('In Bearbeitung', $byCategory[ProjectStatusCategory::Active->value]);
        $this->assertSame('Erledigt', $byCategory[ProjectStatusCategory::Done->value]);
    }

    public function test_execute_uses_chat_attachment_as_logo_copying_the_file(): void
    {
        Storage::fake('public');
        [$user, $team] = $this->userInTeam();

        // Pre-place a fake image on the public disk where chat attachments live.
        $source = UploadedFile::fake()->image('logo.png');
        $sourcePath = $source->storePublicly('chat-attachments/'.$user->id.'/_pending', 'public');

        $project = Project::query()->find(app(CreateProjectTool::class)->execute($user, [
            'team_id' => $team->id,
            'name' => 'Designsystem',
            'description' => null,
            'logo_attachment' => [
                'path' => $sourcePath,
                'url' => 'https://test/'.$sourcePath,
                'mime' => 'image/png',
                'name' => 'logo.png',
            ],
        ])['project_id']);

        $this->assertNotNull($project->logo);
        $this->assertStringContainsString("project-logos/{$team->id}/", $project->logo);

        // Source file is NOT moved away — copy semantics.
        $this->assertTrue(Storage::disk('public')->exists($sourcePath));
    }

    public function test_execute_throws_when_user_lost_team_access_between_validate_and_execute(): void
    {
        [$user, $team] = $this->userInTeam();
        // Detach the user — simulating concurrent role revocation between
        // preview and confirm.
        $team->members()->detach($user->id);

        $this->expectException(ModelNotFoundException::class);

        app(CreateProjectTool::class)->execute($user, [
            'team_id' => $team->id,
            'name' => 'Too late',
            'description' => null,
            'logo_attachment' => null,
        ]);
    }
}
