<?php

namespace Tests\Unit\Domains\Preferences\Actions;

use App\Domains\Preferences\Actions\FindUserPreferenceAction;
use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUserPreferenceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_matching_preference(): void
    {
        $user = User::factory()->create();
        $pref = UserPreference::create([
            'user_id' => $user->id,
            'key' => 'working_hours',
            'value' => ['monday' => []],
        ]);

        $found = (new FindUserPreferenceAction)->execute($user->id, 'working_hours');

        $this->assertNotNull($found);
        $this->assertSame($pref->id, $found->id);
        $this->assertSame(['monday' => []], $found->value);
    }

    public function test_returns_null_when_absent(): void
    {
        $user = User::factory()->create();

        $this->assertNull((new FindUserPreferenceAction)->execute($user->id, 'working_hours'));
    }

    public function test_scopes_by_user_id(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        UserPreference::create([
            'user_id' => $owner->id,
            'key' => 'working_hours',
            'value' => ['monday' => [['start' => '09:00', 'end' => '17:00']]],
        ]);

        $this->assertNull((new FindUserPreferenceAction)->execute($other->id, 'working_hours'));
    }
}
