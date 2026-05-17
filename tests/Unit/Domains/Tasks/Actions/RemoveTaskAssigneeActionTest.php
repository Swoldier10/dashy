<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Tasks\Actions\RemoveTaskAssigneeAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveTaskAssigneeActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_detaches_only_the_specified_user(): void
    {
        $task = Task::factory()->create();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $task->assignees()->attach([$a->id, $b->id]);

        (new RemoveTaskAssigneeAction)->execute($task, $a->id);

        $this->assertSame([$b->id], $task->assignees()->pluck('users.id')->all());
    }
}
