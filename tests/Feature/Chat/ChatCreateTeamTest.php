<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ChatCreateTeamTest extends TestCase
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

    private function mockStreamedToolCall(string $callId, string $arguments): void
    {
        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->once()->andReturnUsing(function () use ($callId, $arguments) {
            yield ChatStreamEvent::toolCallStarted($callId, 'create_team');
            yield ChatStreamEvent::toolCallCompleted($callId, 'create_team', $arguments);
        });
        $this->app->instance(CodexClient::class, $mock);
    }

    public function test_tool_call_persisted_as_pending_then_confirm_creates_team(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $this->mockStreamedToolCall('fc_t1', json_encode(['name' => 'Marketing Crew']));

        Livewire::test('chat.chat-panel')
            ->set('message', 'create a team called Marketing Crew')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertNotNull($assistant->tool_call);
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertSame('create_team', $assistant->tool_call['name']);
        // Proper noun — passed through verbatim, never translated.
        $this->assertSame('Marketing Crew', $assistant->tool_call['arguments']['name']);
        $this->assertSame(0, Team::count(), 'Tool call must NOT auto-execute.');

        Livewire::test('chat.chat-panel', ['chat' => $assistant->chat_id])
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(1, Team::count());
        $team = Team::firstOrFail();
        $this->assertSame('Marketing Crew', $team->name);
        $this->assertFalse($team->personal_team);
        $this->assertSame(
            TeamRole::Owner->value,
            $team->members()->whereKey($user->id)->firstOrFail()->pivot->role->value,
        );

        $assistant->refresh();
        $this->assertSame('created', $assistant->tool_call['status']);
        $this->assertSame($team->id, $assistant->tool_call['result']['team_id']);
    }

    public function test_create_team_from_chat_attachment_sets_logo(): void
    {
        Storage::fake('public');
        $user = $this->userWithCodex();
        $this->actingAs($user);

        // Pre-place an image on the public disk and reference it from a user
        // message, the way the chat composer's upload flow stores attachments.
        $sourcePath = UploadedFile::fake()->image('logo.png')
            ->storePublicly('chat-attachments/'.$user->id.'/_pending', 'public');
        $chat = Chat::create(['user_id' => $user->id]);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'here is our logo',
            'attachments' => [[
                'type' => 'image',
                'path' => $sourcePath,
                'url' => Storage::disk('public')->url($sourcePath),
                'mime' => 'image/png',
                'name' => 'logo.png',
            ]],
        ]);

        $this->mockStreamedToolCall('fc_t2', json_encode(['name' => 'Acme']));

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('message', 'create a team called Acme with that logo')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $assistant = Message::where('role', 'assistant')->firstOrFail();
        $this->assertSame('pending', $assistant->tool_call['status']);
        $this->assertSame($sourcePath, $assistant->tool_call['arguments']['logo_attachment']['path']);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('confirmToolCall', $assistant->id);

        $team = Team::firstOrFail();
        $this->assertNotNull($team->logo);
        $this->assertStringContainsString("team-logos/{$team->id}/", $team->logo);
        // Copy semantics — the chat attachment stays in place.
        $this->assertTrue(Storage::disk('public')->exists($sourcePath));
    }

    public function test_discard_does_not_create_team(): void
    {
        $user = $this->userWithCodex();
        $this->actingAs($user);

        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_t3',
                'name' => 'create_team',
                'arguments' => [
                    'name' => 'Will discard',
                    'logo_attachment' => null,
                ],
                'status' => 'pending',
            ],
        ]);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('discardToolCall', $message->id);

        $message->refresh();
        $this->assertSame('discarded', $message->tool_call['status']);
        $this->assertSame(0, Team::count());
    }
}
