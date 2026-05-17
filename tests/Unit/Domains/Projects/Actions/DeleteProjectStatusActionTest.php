<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\DeleteProjectStatusAction;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProjectStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_status(): void
    {
        $status = ProjectStatus::factory()->create();

        (new DeleteProjectStatusAction)->execute($status);

        $this->assertSame(0, ProjectStatus::count());
    }
}
