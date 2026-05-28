<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindInvitationByTokenHashAction;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindInvitationByTokenHashActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_by_hash_and_eager_loads_team(): void
    {
        $hash = str_repeat('b', 64);
        $invitation = TeamInvitation::factory()->create(['token_hash' => $hash]);

        $found = (new FindInvitationByTokenHashAction)->execute($hash);

        $this->assertNotNull($found);
        $this->assertTrue($found->is($invitation));
        $this->assertTrue($found->relationLoaded('team'));
        $this->assertTrue($found->relationLoaded('invitedBy'));
    }

    public function test_returns_null_when_no_match(): void
    {
        $this->assertNull((new FindInvitationByTokenHashAction)->execute(str_repeat('z', 64)));
    }
}
