<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\FindProjectStatusAction;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindProjectStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_status_when_found(): void
    {
        $status = ProjectStatus::factory()->create();

        $found = (new FindProjectStatusAction)->execute($status->id);

        $this->assertTrue($status->is($found));
    }

    public function test_throws_when_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new FindProjectStatusAction)->execute(99999);
    }
}
