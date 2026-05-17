<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Chat\Services\ConfirmToolCallService;
use App\Domains\Chat\Services\DiscardToolCallService;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ConfirmToolCallServiceTest extends TestCase
{
    use RefreshDatabase;

    private function pendingMessage(User $user): array
    {
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
        ]);

        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => 'Sure, creating that.',
            'tool_call' => [
                'tool_call_id' => 'fc_1',
                'name' => 'create_task',
                'arguments' => [
                    'project_id' => $project->id,
                    'name' => 'Pending task',
                    'description' => null,
                    'status_id' => $status->id,
                    'priority' => 'normal',
                    'start_date' => '2026-05-10',
                    'end_date' => null,
                    'assignee_user_ids' => [$user->id],
                ],
                'status' => 'pending',
            ],
        ]);

        return [$message, $project, $status];
    }

    public function test_confirm_executes_tool_and_marks_created(): void
    {
        $user = User::factory()->create();
        [$message] = $this->pendingMessage($user);

        $payload = app(ConfirmToolCallService::class)->execute($user, $message->id);

        $this->assertSame('created', $payload['status']);
        $this->assertSame(1, Task::count());
        $this->assertSame('Pending task', Task::first()->name);

        $message->refresh();
        $this->assertSame('created', $message->tool_call['status']);
        $this->assertSame(Task::first()->id, $message->tool_call['result']['task_id']);
    }

    public function test_confirm_rejects_already_confirmed_message(): void
    {
        $user = User::factory()->create();
        [$message] = $this->pendingMessage($user);

        app(ConfirmToolCallService::class)->execute($user, $message->id);

        $this->expectException(RuntimeException::class);
        app(ConfirmToolCallService::class)->execute($user, $message->id);
    }

    public function test_confirm_rejects_cross_user_message(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        [$message] = $this->pendingMessage($owner);

        $this->expectException(ModelNotFoundException::class);
        app(ConfirmToolCallService::class)->execute($intruder, $message->id);
    }

    public function test_confirm_rejects_user_role_message(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $message = Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'hi',
            'tool_call' => ['name' => 'create_task', 'arguments' => [], 'status' => 'pending'],
        ]);

        $this->expectException(AuthorizationException::class);
        app(ConfirmToolCallService::class)->execute($user, $message->id);
    }

    public function test_discard_marks_discarded_without_executing(): void
    {
        $user = User::factory()->create();
        [$message] = $this->pendingMessage($user);

        app(DiscardToolCallService::class)->execute($user, $message->id);

        $this->assertSame(0, Task::count());
        $message->refresh();
        $this->assertSame('discarded', $message->tool_call['status']);
    }

    public function test_confirm_applies_edits_to_arguments_before_execute(): void
    {
        $user = User::factory()->create();
        [$message] = $this->pendingMessage($user);

        app(ConfirmToolCallService::class)->execute($user, $message->id, [
            'name' => 'Edited task',
            'priority' => 'high',
        ]);

        $this->assertSame(1, Task::count());
        $task = Task::first();
        $this->assertSame('Edited task', $task->name);
        $this->assertSame('high', $task->priority->value);

        $message->refresh();
        $this->assertSame('Edited task', $message->tool_call['arguments']['name']);
        $this->assertSame('high', $message->tool_call['arguments']['priority']);
        $this->assertSame([], $message->tool_call['validation_errors']);
    }

    public function test_confirm_with_invalid_edits_keeps_pending_with_validation_errors(): void
    {
        $user = User::factory()->create();
        [$message] = $this->pendingMessage($user);

        $payload = app(ConfirmToolCallService::class)->execute($user, $message->id, [
            'name' => '   ',
        ]);

        $this->assertSame('pending', $payload['status']);
        $this->assertNotEmpty($payload['validation_errors']);
        $this->assertSame(0, Task::count());

        $message->refresh();
        $this->assertSame('pending', $message->tool_call['status']);
        $this->assertNotEmpty($message->tool_call['validation_errors']);
        // The user's typed edit is preserved so the form can show what they had.
        $this->assertSame('   ', $message->tool_call['arguments']['name']);
    }

    public function test_confirm_coerces_string_ids_from_form_inputs(): void
    {
        $user = User::factory()->create();
        [$message, $project, $status] = $this->pendingMessage($user);
        $extraAssignee = User::factory()->create();
        $project->team->members()->attach($extraAssignee->id, ['role' => TeamRole::Member->value]);

        // Form posts arrive as strings; the service must coerce them so the
        // tool's validator sees correct types.
        app(ConfirmToolCallService::class)->execute($user, $message->id, [
            'status_id' => (string) $status->id,
            'assignee_user_ids' => [(string) $user->id, (string) $extraAssignee->id],
        ]);

        $task = Task::first();
        $this->assertNotNull($task);
        $this->assertEqualsCanonicalizing(
            [$user->id, $extraAssignee->id],
            $task->assignees()->pluck('users.id')->all(),
        );
    }
}
