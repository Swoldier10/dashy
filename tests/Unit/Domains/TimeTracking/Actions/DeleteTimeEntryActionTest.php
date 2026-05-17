<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Actions\DeleteTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTimeEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_entry(): void
    {
        $entry = TimeEntry::factory()->create();

        (new DeleteTimeEntryAction)->execute($entry);

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }
}
