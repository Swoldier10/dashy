<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindInvitationAction;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindInvitationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_by_id(): void
    {
        $invitation = TeamInvitation::factory()->create();

        $found = (new FindInvitationAction)->execute($invitation->id);

        $this->assertNotNull($found);
        $this->assertTrue($found->is($invitation));
    }

    public function test_returns_null_for_missing_id(): void
    {
        $this->assertNull((new FindInvitationAction)->execute(999999));
    }
}
