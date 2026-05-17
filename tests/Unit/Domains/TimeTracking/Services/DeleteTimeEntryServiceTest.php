<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\DeleteTimeEntryService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTimeEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($user)->create();

        app(DeleteTimeEntryService::class)->execute($user, $entry);

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }

    public function test_stranger_cannot_delete(): void
    {
        $owner = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($owner)->create();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(DeleteTimeEntryService::class)->execute($stranger, $entry);
    }
}
