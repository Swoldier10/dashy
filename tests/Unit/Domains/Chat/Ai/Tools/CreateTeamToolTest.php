<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Tools\CreateTeamTool;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateTeamToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_rejects_missing_name(): void
    {
        $user = User::factory()->create();

        $result = app(CreateTeamTool::class)->validate($user, []);

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->errors);
    }

    public function test_validate_rejects_empty_name(): void
    {
        $user = User::factory()->create();

        $result = app(CreateTeamTool::class)->validate($user, ['name' => '   ']);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_name_longer_than_eighty_chars(): void
    {
        $user = User::factory()->create();

        $result = app(CreateTeamTool::class)->validate($user, ['name' => str_repeat('a', 81)]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_happy_path_returns_trimmed_verbatim_name_and_null_logo(): void
    {
        $user = User::factory()->create();

        // An English name must pass through untranslated — team names are
        // proper nouns, unlike the German-content rules for projects/tasks.
        $result = app(CreateTeamTool::class)->validate($user, ['name' => '  Marketing Crew  ']);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('Marketing Crew', $result->normalized['name']);
        $this->assertNull($result->normalized['logo_attachment']);
    }

    public function test_validate_snapshots_first_image_from_latest_user_message(): void
    {
        $user = User::factory()->create();
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

        $result = app(CreateTeamTool::class)->validate($user, ['name' => 'Acme'], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('chat-attachments/x/first.png', $result->normalized['logo_attachment']['path']);
        $this->assertSame('first.png', $result->normalized['logo_attachment']['name']);
    }

    public function test_validate_preserves_existing_logo_attachment_during_revalidation(): void
    {
        $user = User::factory()->create();
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

        $result = app(CreateTeamTool::class)->validate($user, [
            'name' => 'Acme',
            'logo_attachment' => [
                'path' => 'chat-attachments/x/original.png',
                'url' => 'https://test/original.png',
                'mime' => 'image/png',
                'name' => 'original.png',
            ],
        ], $chat);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('chat-attachments/x/original.png', $result->normalized['logo_attachment']['path']);
        $this->assertSame('original.png', $result->normalized['logo_attachment']['name']);
    }

    public function test_validate_treats_explicit_null_logo_attachment_as_cleared(): void
    {
        $user = User::factory()->create();
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

        $result = app(CreateTeamTool::class)->validate($user, [
            'name' => 'Acme',
            'logo_attachment' => null,
        ], $chat);

        $this->assertTrue($result->valid);
        $this->assertNull($result->normalized['logo_attachment']);
    }

    public function test_execute_creates_team_and_attaches_owner(): void
    {
        $user = User::factory()->create();

        $result = app(CreateTeamTool::class)->execute($user, [
            'name' => 'Marketing Crew',
            'logo_attachment' => null,
        ]);

        $team = Team::query()->findOrFail($result['team_id']);
        $this->assertSame('Marketing Crew', $team->name);
        $this->assertSame('Marketing Crew', $result['name']);
        $this->assertFalse($team->personal_team);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => TeamRole::Owner->value,
        ]);
    }

    public function test_execute_uses_chat_attachment_as_logo_copying_the_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Pre-place a fake image on the public disk where chat attachments live.
        $source = UploadedFile::fake()->image('logo.png');
        $sourcePath = $source->storePublicly('chat-attachments/'.$user->id.'/_pending', 'public');

        $result = app(CreateTeamTool::class)->execute($user, [
            'name' => 'Acme',
            'logo_attachment' => [
                'path' => $sourcePath,
                'url' => 'https://test/'.$sourcePath,
                'mime' => 'image/png',
                'name' => 'logo.png',
            ],
        ]);

        $team = Team::query()->findOrFail($result['team_id']);
        $this->assertNotNull($team->logo);
        $this->assertStringContainsString("team-logos/{$team->id}/", $team->logo);

        // Source file is NOT moved away — copy semantics.
        $this->assertTrue(Storage::disk('public')->exists($sourcePath));
    }

    public function test_execute_ignores_logo_snapshot_whose_file_no_longer_exists(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $result = app(CreateTeamTool::class)->execute($user, [
            'name' => 'Acme',
            'logo_attachment' => [
                'path' => 'chat-attachments/x/gone.png',
                'url' => 'https://test/gone.png',
                'mime' => 'image/png',
                'name' => 'gone.png',
            ],
        ]);

        $team = Team::query()->findOrFail($result['team_id']);
        $this->assertNull($team->logo);
    }
}
