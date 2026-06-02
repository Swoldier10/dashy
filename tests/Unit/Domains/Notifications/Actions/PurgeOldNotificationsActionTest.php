<?php

namespace Tests\Unit\Domains\Notifications\Actions;

use App\Domains\Notifications\Actions\PurgeOldNotificationsAction;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeOldNotificationsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_old_read_and_ancient_rows_but_keeps_the_rest(): void
    {
        $user = User::factory()->create();
        $readBefore = CarbonImmutable::now()->subDays(30);
        $allBefore = CarbonImmutable::now()->subDays(90);

        $oldRead = Notification::factory()->for($user, 'recipient')->create([
            'read_at' => now()->subDays(31),
            'created_at' => now()->subDays(31),
        ]);
        $recentRead = Notification::factory()->for($user, 'recipient')->create([
            'read_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
        ]);
        $ancientUnread = Notification::factory()->for($user, 'recipient')->create([
            'created_at' => now()->subDays(91),
        ]);
        $recentUnread = Notification::factory()->for($user, 'recipient')->create([
            'created_at' => now()->subDays(60),
        ]);

        $deleted = (new PurgeOldNotificationsAction)->execute($readBefore, $allBefore);

        $this->assertSame(2, $deleted);
        $this->assertNull($oldRead->fresh());
        $this->assertNull($ancientUnread->fresh());
        $this->assertNotNull($recentRead->fresh());
        $this->assertNotNull($recentUnread->fresh());
    }

    public function test_returns_zero_when_nothing_qualifies(): void
    {
        $user = User::factory()->create();
        Notification::factory()->for($user, 'recipient')->create();

        $deleted = (new PurgeOldNotificationsAction)->execute(
            CarbonImmutable::now()->subDays(30),
            CarbonImmutable::now()->subDays(90),
        );

        $this->assertSame(0, $deleted);
    }
}
