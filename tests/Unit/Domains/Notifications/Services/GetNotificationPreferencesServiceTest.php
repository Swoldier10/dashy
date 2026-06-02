<?php

namespace Tests\Unit\Domains\Notifications\Services;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\GetNotificationPreferencesService;
use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetNotificationPreferencesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_enum_defaults_when_nothing_is_stored(): void
    {
        $user = User::factory()->create();

        $preferences = app(GetNotificationPreferencesService::class)->execute($user->id);

        $this->assertCount(count(NotificationType::cases()), $preferences);
        $this->assertSame(['email' => true, 'app' => true], $preferences['task_assigned']);
        $this->assertSame(['email' => false, 'app' => false], $preferences['task_created_in_project']);
    }

    public function test_stored_choices_override_the_defaults(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'key' => GetNotificationPreferencesService::PREFERENCE_KEY,
            'value' => ['task_assigned' => ['email' => false, 'app' => true]],
        ]);

        $preferences = app(GetNotificationPreferencesService::class)->execute($user->id);

        $this->assertSame(['email' => false, 'app' => true], $preferences['task_assigned']);
        // Untouched types keep their defaults.
        $this->assertSame(['email' => true, 'app' => true], $preferences['task_due_soon']);
    }

    public function test_malformed_stored_entries_degrade_to_defaults(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'key' => GetNotificationPreferencesService::PREFERENCE_KEY,
            'value' => [
                'task_assigned' => 'yes',
                'unknown_type' => ['email' => false, 'app' => false],
                'task_overdue' => ['email' => 'nope', 'app' => false],
            ],
        ]);

        $preferences = app(GetNotificationPreferencesService::class)->execute($user->id);

        $this->assertSame(['email' => true, 'app' => true], $preferences['task_assigned']);
        $this->assertArrayNotHasKey('unknown_type', $preferences);
        // Valid boolean applies, invalid one falls back to the default.
        $this->assertSame(['email' => true, 'app' => false], $preferences['task_overdue']);
    }
}
