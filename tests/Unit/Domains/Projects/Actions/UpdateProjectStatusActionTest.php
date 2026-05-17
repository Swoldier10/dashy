<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\UpdateProjectStatusAction;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProjectStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_provided_attributes(): void
    {
        $status = ProjectStatus::factory()->create(['name' => 'OLD']);

        $updated = (new UpdateProjectStatusAction)->execute($status, ['name' => 'NEW']);

        $this->assertSame('NEW', $updated->name);
    }
}
