<?php

namespace Tests\Unit\Domains\Preferences\Actions;

use App\Domains\Preferences\Actions\ListUserPreferencesAction;
use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListUserPreferencesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_all_preferences_for_the_user_ordered_by_key(): void
    {
        $user = User::factory()->create();
        UserPreference::create(['user_id' => $user->id, 'key' => 'zeta', 'value' => [1]]);
        UserPreference::create(['user_id' => $user->id, 'key' => 'alpha', 'value' => [2]]);
        UserPreference::create(['user_id' => User::factory()->create()->id, 'key' => 'other', 'value' => [3]]);

        $prefs = (new ListUserPreferencesAction)->execute($user->id);

        $this->assertSame(['alpha', 'zeta'], $prefs->pluck('key')->all());
    }

    public function test_filters_by_key_prefix(): void
    {
        $user = User::factory()->create();
        UserPreference::create(['user_id' => $user->id, 'key' => 'memory.a', 'value' => [1]]);
        UserPreference::create(['user_id' => $user->id, 'key' => 'working_hours', 'value' => [2]]);

        $prefs = (new ListUserPreferencesAction)->execute($user->id, 'memory.');

        $this->assertSame(['memory.a'], $prefs->pluck('key')->all());
    }
}
