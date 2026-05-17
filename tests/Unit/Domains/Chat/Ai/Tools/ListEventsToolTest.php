<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Tools\ListEventsTool;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListEventsToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_requires_from(): void
    {
        $user = User::factory()->create();

        $result = app(ListEventsTool::class)->validate($user, []);

        $this->assertFalse($result->valid);
    }

    public function test_validate_defaults_to_to_from_plus_seven_days(): void
    {
        $user = User::factory()->create();

        $result = app(ListEventsTool::class)->validate($user, ['from' => '2026-06-01']);

        $this->assertTrue($result->valid);
        $this->assertSame('2026-06-01', $result->normalized['from']);
        $this->assertSame(
            CarbonImmutable::parse('2026-06-01')->addDays(7)->toDateString(),
            $result->normalized['to'],
        );
    }

    public function test_validate_rejects_to_before_from(): void
    {
        $user = User::factory()->create();

        $result = app(ListEventsTool::class)->validate($user, [
            'from' => '2026-06-15',
            'to' => '2026-06-01',
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_clamps_range_to_ninety_days(): void
    {
        $user = User::factory()->create();

        $result = app(ListEventsTool::class)->validate($user, [
            'from' => '2026-01-01',
            'to' => '2026-12-31',
        ]);

        $this->assertTrue($result->valid);
        $this->assertSame(
            CarbonImmutable::parse('2026-01-01')->addDays(90)->toDateString(),
            $result->normalized['to'],
        );
    }
}
