<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Actions\FindTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTimeEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_entry_when_found(): void
    {
        $entry = TimeEntry::factory()->create();

        $found = (new FindTimeEntryAction)->execute($entry->id);

        $this->assertTrue($entry->is($found));
    }

    public function test_throws_when_missing(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new FindTimeEntryAction)->execute(999_999);
    }
}
