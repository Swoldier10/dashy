<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\FindTimeEntryService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTimeEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_find_their_entry(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $found = app(FindTimeEntryService::class)->execute($user, $entry->id);

        $this->assertTrue($entry->is($found));
    }

    public function test_stranger_cannot_find(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $owner->id]);

        $this->expectException(AuthorizationException::class);

        app(FindTimeEntryService::class)->execute($stranger, $entry->id);
    }
}
