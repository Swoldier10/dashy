<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_provided_attributes_only(): void
    {
        $task = Task::factory()->create([
            'name' => 'Old',
            'description' => 'Old desc',
        ]);

        $updated = (new UpdateTaskAction)->execute($task, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
        $this->assertSame('Old desc', $updated->description);
    }
}
