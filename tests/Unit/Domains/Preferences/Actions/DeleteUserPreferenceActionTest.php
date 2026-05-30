<?php

namespace Tests\Unit\Domains\Preferences\Actions;

use App\Domains\Preferences\Actions\DeleteUserPreferenceAction;
use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUserPreferenceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_the_matching_key_and_returns_the_count(): void
    {
        $user = User::factory()->create();
        UserPreference::create(['user_id' => $user->id, 'key' => 'memory.x', 'value' => ['f' => 1]]);

        $deleted = (new DeleteUserPreferenceAction)->execute($user->id, 'memory.x');

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('user_preferences', ['user_id' => $user->id, 'key' => 'memory.x']);
    }

    public function test_returns_zero_when_no_row_matches(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0, (new DeleteUserPreferenceAction)->execute($user->id, 'memory.absent'));
    }
}
