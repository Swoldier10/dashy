<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Actions\UpdateTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTimeEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_provided_attributes(): void
    {
        $entry = TimeEntry::factory()->create([
            'duration_seconds' => 600,
            'notes' => 'old',
        ]);

        $updated = (new UpdateTimeEntryAction)->execute($entry, [
            'duration_seconds' => 900,
            'notes' => 'new',
        ]);

        $this->assertSame(900, $updated->duration_seconds);
        $this->assertSame('new', $updated->notes);
    }
}
