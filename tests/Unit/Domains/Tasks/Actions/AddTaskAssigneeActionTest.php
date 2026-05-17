<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Tasks\Actions\AddTaskAssigneeAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddTaskAssigneeActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_attaches_user_with_pivot_metadata(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $actor = User::factory()->create();

        (new AddTaskAssigneeAction)->execute($task, $user->id, $actor->id);

        $pivot = $task->assignees()->where('users.id', $user->id)->first()?->pivot;
        $this->assertNotNull($pivot);
        $this->assertSame($actor->id, (int) $pivot->assigned_by_user_id);
    }

    public function test_is_idempotent(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        (new AddTaskAssigneeAction)->execute($task, $user->id);
        (new AddTaskAssigneeAction)->execute($task, $user->id);

        $this->assertSame(1, $task->assignees()->count());
    }
}
