<?php

namespace Tests\Unit\Domains\Preferences\Services;

use App\Domains\Preferences\Models\UserPreference;
use App\Domains\Preferences\Services\SetUserPreferenceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetUserPreferenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_a_new_value(): void
    {
        $user = User::factory()->create();

        app(SetUserPreferenceService::class)->execute($user->id, 'lang', ['code' => 'de']);

        $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id, 'key' => 'lang']);
    }

    public function test_overwrites_an_existing_value_without_duplicating_the_row(): void
    {
        $user = User::factory()->create();
        $service = app(SetUserPreferenceService::class);

        $service->execute($user->id, 'lang', ['code' => 'de']);
        $service->execute($user->id, 'lang', ['code' => 'en']);

        $rows = UserPreference::query()->where('user_id', $user->id)->where('key', 'lang')->get();
        $this->assertCount(1, $rows);
        $this->assertSame(['code' => 'en'], $rows->first()->value);
    }
}
