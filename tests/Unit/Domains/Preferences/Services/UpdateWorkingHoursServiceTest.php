<?php

namespace Tests\Unit\Domains\Preferences\Services;

use App\Domains\Preferences\Models\UserPreference;
use App\Domains\Preferences\Services\UpdateWorkingHoursService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateWorkingHoursServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): UpdateWorkingHoursService
    {
        return app(UpdateWorkingHoursService::class);
    }

    public function test_persists_valid_week_with_mixed_days(): void
    {
        $user = User::factory()->create();

        $result = $this->service()->execute($user, [
            'monday' => [
                ['start' => '09:00', 'end' => '12:00'],
                ['start' => '13:00', 'end' => '17:00'],
            ],
            'wednesday' => [
                ['start' => '10:00', 'end' => '14:00'],
            ],
        ]);

        $this->assertSame([
            ['start' => '09:00', 'end' => '12:00'],
            ['start' => '13:00', 'end' => '17:00'],
        ], $result['monday']);
        $this->assertSame([], $result['tuesday']);
        $this->assertSame([['start' => '10:00', 'end' => '14:00']], $result['wednesday']);
        $this->assertSame([], $result['sunday']);

        $pref = UserPreference::query()
            ->where('user_id', $user->id)
            ->where('key', 'working_hours')
            ->first();
        $this->assertNotNull($pref);
        $this->assertSame($result, $pref->value);
    }

    public function test_updates_existing_preference_in_place(): void
    {
        $user = User::factory()->create();

        UserPreference::create([
            'user_id' => $user->id,
            'key' => 'working_hours',
            'value' => ['monday' => [['start' => '08:00', 'end' => '10:00']]],
        ]);

        $this->service()->execute($user, [
            'monday' => [['start' => '09:00', 'end' => '17:00']],
        ]);

        $rows = UserPreference::query()
            ->where('user_id', $user->id)
            ->where('key', 'working_hours')
            ->get();

        $this->assertCount(1, $rows);
        $this->assertSame([['start' => '09:00', 'end' => '17:00']], $rows->first()->value['monday']);
    }

    public function test_rejects_unknown_day_key(): void
    {
        $user = User::factory()->create();

        try {
            $this->service()->execute($user, [
                'mondday' => [['start' => '09:00', 'end' => '17:00']],
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('hours', $e->errors());
        }
    }

    public function test_rejects_malformed_time(): void
    {
        $user = User::factory()->create();

        try {
            $this->service()->execute($user, [
                'monday' => [['start' => '9:00', 'end' => '17:00']],
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('hours.monday.0.start', $e->errors());
        }
    }

    public function test_rejects_out_of_range_hour(): void
    {
        $user = User::factory()->create();

        try {
            $this->service()->execute($user, [
                'monday' => [['start' => '09:00', 'end' => '24:00']],
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('hours.monday.0.end', $e->errors());
        }
    }

    public function test_rejects_end_not_after_start(): void
    {
        $user = User::factory()->create();

        try {
            $this->service()->execute($user, [
                'monday' => [['start' => '17:00', 'end' => '09:00']],
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('hours.monday.0.end', $e->errors());
        }
    }

    public function test_rejects_equal_start_and_end(): void
    {
        $user = User::factory()->create();

        try {
            $this->service()->execute($user, [
                'monday' => [['start' => '09:00', 'end' => '09:00']],
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('hours.monday.0.end', $e->errors());
        }
    }

    public function test_rejects_overlapping_ranges_within_day(): void
    {
        $user = User::factory()->create();

        try {
            $this->service()->execute($user, [
                'monday' => [
                    ['start' => '09:00', 'end' => '13:00'],
                    ['start' => '12:00', 'end' => '17:00'],
                ],
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('hours.monday', $e->errors());
        }
    }

    public function test_sorts_ranges_by_start_before_persisting(): void
    {
        $user = User::factory()->create();

        $result = $this->service()->execute($user, [
            'monday' => [
                ['start' => '13:00', 'end' => '17:00'],
                ['start' => '09:00', 'end' => '12:00'],
            ],
        ]);

        $this->assertSame([
            ['start' => '09:00', 'end' => '12:00'],
            ['start' => '13:00', 'end' => '17:00'],
        ], $result['monday']);
    }

    public function test_empty_payload_persists_all_days_off(): void
    {
        $user = User::factory()->create();

        $result = $this->service()->execute($user, []);

        foreach (UpdateWorkingHoursService::DAYS as $day) {
            $this->assertSame([], $result[$day]);
        }

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'key' => 'working_hours',
        ]);
    }
}
