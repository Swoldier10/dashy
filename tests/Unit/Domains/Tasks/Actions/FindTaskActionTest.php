<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Models\Task;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_task_with_relations(): void
    {
        $task = Task::factory()->create();

        $found = (new FindTaskAction)->execute($task->id);

        $this->assertTrue($found->relationLoaded('status'));
        $this->assertTrue($found->relationLoaded('assignees'));
        $this->assertTrue($found->relationLoaded('creator'));
        $this->assertTrue($found->relationLoaded('project'));
    }

    public function test_throws_when_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new FindTaskAction)->execute(999_999);
    }
}
