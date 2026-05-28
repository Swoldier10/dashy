<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\UpdateInvitationResentAction;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UpdateInvitationResentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rotates_token_and_extends_expiry(): void
    {
        Carbon::setTestNow('2026-01-15 10:00:00');

        $oldHash = str_repeat('1', 64);
        $invitation = TeamInvitation::factory()->create([
            'token_hash' => $oldHash,
            'last_sent_at' => now()->subDays(2),
            'expires_at' => now()->addDays(5),
        ]);

        $newHash = str_repeat('2', 64);
        $updated = (new UpdateInvitationResentAction)->execute($invitation, $newHash);

        $this->assertSame($newHash, $updated->token_hash);
        $this->assertTrue($updated->last_sent_at->equalTo(now()));
        $this->assertTrue($updated->expires_at->equalTo(now()->addDays(7)));

        Carbon::setTestNow();
    }
}
