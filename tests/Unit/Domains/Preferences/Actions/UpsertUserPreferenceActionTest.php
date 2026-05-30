<?php

namespace Tests\Unit\Domains\Preferences\Actions;

use App\Domains\Preferences\Actions\UpsertUserPreferenceAction;
use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpsertUserPreferenceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_preference_when_none_exists(): void
    {
        $user = User::factory()->create();

        $pref = (new UpsertUserPreferenceAction)->execute($user->id, 'lang', ['code' => 'de']);

        $this->assertSame(['code' => 'de'], $pref->value);
        $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id, 'key' => 'lang']);
    }

    public function test_updates_the_existing_row_for_the_same_key(): void
    {
        $user = User::factory()->create();
        (new UpsertUserPreferenceAction)->execute($user->id, 'lang', ['code' => 'de']);

        (new UpsertUserPreferenceAction)->execute($user->id, 'lang', ['code' => 'en']);

        $this->assertSame(1, UserPreference::query()->where('user_id', $user->id)->where('key', 'lang')->count());
        $this->assertSame(['code' => 'en'], UserPreference::query()->where('user_id', $user->id)->where('key', 'lang')->first()->value);
    }
}
