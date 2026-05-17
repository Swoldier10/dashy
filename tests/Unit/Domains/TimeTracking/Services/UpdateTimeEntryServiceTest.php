<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\UpdateTimeEntryService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTimeEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_duration_and_notes(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($user)->create([
            'duration_seconds' => 600,
        ]);

        $updated = app(UpdateTimeEntryService::class)->execute($user, $entry, [
            'duration' => '45m',
            'notes' => 'fixed estimate',
        ]);

        $this->assertSame(45 * 60, $updated->duration_seconds);
        $this->assertSame('fixed estimate', $updated->notes);
    }

    public function test_cannot_edit_running_entry(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($user)->running()->create();

        $this->expectException(ValidationException::class);
        app(UpdateTimeEntryService::class)->execute($user, $entry, [
            'duration' => '1h',
        ]);
    }

    public function test_non_owner_non_member_cannot_update(): void
    {
        $owner = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($owner)->create();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(UpdateTimeEntryService::class)->execute($stranger, $entry, [
            'notes' => 'snoop',
        ]);
    }
}
