<?php

namespace Tests\Feature\Chat;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\Chat\Ai\Tools\ListEventsTool;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatEventToolsTest extends TestCase
{
    use RefreshDatabase;

    private function seedUserAndChat(): array
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $chat = Chat::create(['user_id' => $user->id]);

        return [$user, $chat];
    }

    private function pendingCreateEventMessage(Chat $chat, array $overrides = []): Message
    {
        return Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_event_create',
                'name' => 'create_event',
                'arguments' => array_merge([
                    'title' => 'Demo planen',
                    'description' => null,
                    'start_at' => '2026-06-15T09:00',
                    'end_at' => '2026-06-15T10:00',
                    'is_all_day' => false,
                    'color' => EventColor::Danube->value,
                    'location' => null,
                    'recurrence_freq' => RecurrenceFreq::None->value,
                    'recurrence_until' => null,
                ], $overrides),
                'status' => 'pending',
            ],
        ]);
    }

    public function test_confirm_create_event_persists_event(): void
    {
        [$user, $chat] = $this->seedUserAndChat();
        $assistant = $this->pendingCreateEventMessage($chat);

        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->set('toolCallEdits.'.$assistant->id.'.title', 'Demo planen (final)')
            ->set('toolCallEdits.'.$assistant->id.'.start_at', '2026-06-15T09:30')
            ->set('toolCallEdits.'.$assistant->id.'.end_at', '2026-06-15T10:30')
            ->set('toolCallEdits.'.$assistant->id.'.color', EventColor::Shilo->value)
            ->set('toolCallEdits.'.$assistant->id.'.location', 'HQ Raum 2')
            ->call('confirmToolCall', $assistant->id);

        $this->assertSame(1, Event::count());
        $event = Event::first();
        $this->assertSame($user->id, $event->user_id);
        $this->assertSame('Demo planen (final)', $event->title);
        $this->assertSame('HQ Raum 2', $event->location);
        $this->assertSame('2026-06-15 09:30:00', $event->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-15 10:30:00', $event->end_at->format('Y-m-d H:i:s'));
        $this->assertSame(EventColor::Shilo, $event->color);
    }

    public function test_confirm_update_event_changes_fields(): void
    {
        [$user, $chat] = $this->seedUserAndChat();
        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Standup',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 09:30:00',
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);

        $assistant = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_event_update',
                'name' => 'update_event',
                'arguments' => [
                    'event_id' => $event->id,
                    'start_at' => '2026-06-15T10:00',
                    'end_at' => '2026-06-15T10:45',
                    'title' => 'Standup verschoben',
                ],
                'status' => 'pending',
            ],
        ]);

        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('confirmToolCall', $assistant->id);

        $event->refresh();
        $this->assertSame('Standup verschoben', $event->title);
        $this->assertSame('2026-06-15 10:00:00', $event->start_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-15 10:45:00', $event->end_at->format('Y-m-d H:i:s'));
    }

    public function test_confirm_delete_event_removes_event(): void
    {
        [$user, $chat] = $this->seedUserAndChat();
        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Cancelled meeting',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 09:30:00',
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);

        $assistant = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_event_delete',
                'name' => 'delete_event',
                'arguments' => ['event_id' => $event->id],
                'status' => 'pending',
            ],
        ]);

        $this->actingAs($user);

        Livewire::test('chat.chat-panel', ['chat' => $chat->id])
            ->call('confirmToolCall', $assistant->id);

        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_update_event_fails_when_not_owner(): void
    {
        [$owner, $ownerChat] = $this->seedUserAndChat();
        $stranger = User::factory()->create();
        CodexConnection::create([
            'user_id' => $stranger->id,
            'access_token' => 'b',
            'expires_at' => now()->addHour(),
        ]);
        $strangerChat = Chat::create(['user_id' => $stranger->id]);

        $event = Event::create([
            'user_id' => $owner->id,
            'title' => 'Private meeting',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 09:30:00',
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);

        $assistant = Message::create([
            'chat_id' => $strangerChat->id,
            'role' => 'assistant',
            'content' => '',
            'tool_call' => [
                'tool_call_id' => 'fc_event_update_stranger',
                'name' => 'update_event',
                'arguments' => [
                    'event_id' => $event->id,
                    'title' => 'Hacked!',
                ],
                'status' => 'pending',
            ],
        ]);

        $this->actingAs($stranger);

        Livewire::test('chat.chat-panel', ['chat' => $strangerChat->id])
            ->call('confirmToolCall', $assistant->id);

        $assistant->refresh();
        $toolCall = $assistant->tool_call;
        $this->assertSame('pending', $toolCall['status']);
        $this->assertNotEmpty($toolCall['validation_errors'] ?? []);
        $this->assertSame('Private meeting', $event->fresh()->title);
    }

    public function test_list_events_tool_returns_count_and_events(): void
    {
        [$user] = $this->seedUserAndChat();

        Event::create([
            'user_id' => $user->id,
            'title' => 'Inside window',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);
        Event::create([
            'user_id' => $user->id,
            'title' => 'Outside window',
            'start_at' => '2027-01-01 09:00:00',
            'end_at' => '2027-01-01 10:00:00',
            'is_all_day' => false,
            'color' => EventColor::Danube->value,
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);

        $tool = app(ListEventsTool::class);
        $validated = $tool->validate($user, ['from' => '2026-06-01', 'to' => '2026-06-30']);
        $this->assertTrue($validated->valid);

        $result = $tool->execute($user, $validated->normalized);

        $this->assertSame(1, $result['count']);
        $this->assertCount(1, $result['events']);
        $this->assertSame('Inside window', $result['events'][0]['title']);
        $this->assertSame('2026-06-15T09:00', $result['events'][0]['start_at']);
    }

    public function test_list_events_tool_clamps_range_to_90_days(): void
    {
        [$user] = $this->seedUserAndChat();

        $tool = app(ListEventsTool::class);
        $validated = $tool->validate($user, [
            'from' => '2026-01-01',
            'to' => '2026-12-31',
        ]);

        $this->assertTrue($validated->valid);
        $this->assertSame('2026-01-01', $validated->normalized['from']);
        $this->assertSame(
            CarbonImmutable::parse('2026-01-01')->addDays(90)->toDateString(),
            $validated->normalized['to'],
        );
    }
}
