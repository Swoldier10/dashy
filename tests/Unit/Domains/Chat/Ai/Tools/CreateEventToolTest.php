<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Chat\Ai\Tools\CreateEventTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEventToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_requires_title(): void
    {
        $user = User::factory()->create();

        $result = app(CreateEventTool::class)->validate($user, [
            'start_at' => '2026-06-15T09:00',
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_requires_start_at(): void
    {
        $user = User::factory()->create();

        $result = app(CreateEventTool::class)->validate($user, [
            'title' => 'Demo planen',
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_defaults_end_at_to_start_plus_one_hour(): void
    {
        $user = User::factory()->create();

        $result = app(CreateEventTool::class)->validate($user, [
            'title' => 'Demo planen',
            'start_at' => '2026-06-15T09:00',
        ]);

        $this->assertTrue($result->valid);
        $this->assertSame('2026-06-15T09:00', $result->normalized['start_at']);
        $this->assertSame('2026-06-15T10:00', $result->normalized['end_at']);
    }

    public function test_validate_defaults_end_at_when_before_start(): void
    {
        $user = User::factory()->create();

        $result = app(CreateEventTool::class)->validate($user, [
            'title' => 'Demo planen',
            'start_at' => '2026-06-15T09:00',
            'end_at' => '2026-06-15T08:00',
        ]);

        $this->assertTrue($result->valid);
        $this->assertSame('2026-06-15T10:00', $result->normalized['end_at']);
    }

    public function test_validate_drops_recurrence_until_when_freq_is_none(): void
    {
        $user = User::factory()->create();

        $result = app(CreateEventTool::class)->validate($user, [
            'title' => 'Demo planen',
            'start_at' => '2026-06-15T09:00',
            'recurrence_freq' => 'none',
            'recurrence_until' => '2026-12-31',
        ]);

        $this->assertTrue($result->valid);
        $this->assertNull($result->normalized['recurrence_until']);
    }

    public function test_validate_keeps_recurrence_until_when_freq_is_set(): void
    {
        $user = User::factory()->create();

        $result = app(CreateEventTool::class)->validate($user, [
            'title' => 'Demo planen',
            'start_at' => '2026-06-15T09:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
            'recurrence_until' => '2026-12-31',
        ]);

        $this->assertTrue($result->valid);
        $this->assertSame('2026-12-31', $result->normalized['recurrence_until']);
    }

    public function test_validate_falls_back_to_danube_for_invalid_color(): void
    {
        $user = User::factory()->create();

        $result = app(CreateEventTool::class)->validate($user, [
            'title' => 'Demo planen',
            'start_at' => '2026-06-15T09:00',
            'color' => 'puce',
        ]);

        $this->assertTrue($result->valid);
        $this->assertSame(EventColor::Danube->value, $result->normalized['color']);
    }
}
