<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindAndLockInvitationByTokenHashAction;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FindAndLockInvitationByTokenHashActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_inside_a_transaction(): void
    {
        $hash = str_repeat('c', 64);
        $invitation = TeamInvitation::factory()->create(['token_hash' => $hash]);

        $found = DB::transaction(fn () => (new FindAndLockInvitationByTokenHashAction)->execute($hash));

        $this->assertNotNull($found);
        $this->assertTrue($found->is($invitation));
    }

    public function test_returns_null_when_missing(): void
    {
        $this->assertNull(DB::transaction(
            fn () => (new FindAndLockInvitationByTokenHashAction)->execute(str_repeat('y', 64))
        ));
    }
}
