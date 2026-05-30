<?php

namespace Tests\Unit\Domains\Search\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Search\Services\ListBackfillSourcesService;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBackfillSourcesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_scope_yields_tasks_then_projects(): void
    {
        $task = Task::factory()->create();
        $project = Project::factory()->create();

        $pairs = iterator_to_array(
            app(ListBackfillSourcesService::class)->execute('all'),
            false,
        );

        $this->assertContains(['task', $task->id], $pairs);
        $this->assertContains(['project', $project->id], $pairs);
    }

    public function test_invalid_scope_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        iterator_to_array(app(ListBackfillSourcesService::class)->execute('widget'));
    }

    public function test_is_valid_scope_helper(): void
    {
        $this->assertTrue(ListBackfillSourcesService::isValidScope('all'));
        $this->assertFalse(ListBackfillSourcesService::isValidScope('widget'));
        $this->assertSame(['tasks', 'projects', 'messages', 'all'], ListBackfillSourcesService::validScopes());
    }
}
