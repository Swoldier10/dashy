<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Tasks\Actions\DeleteTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_task_and_cascades_assignees(): void
    {
        $task = Task::factory()->create();
        $task->assignees()->attach(User::factory()->create()->id);

        (new DeleteTaskAction)->execute($task);

        $this->assertSame(0, Task::count());
        $this->assertDatabaseCount('task_user', 0);
    }
}
