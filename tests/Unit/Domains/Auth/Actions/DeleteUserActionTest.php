<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\DeleteUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_the_user_row(): void
    {
        $user = User::factory()->create();

        (new DeleteUserAction)->execute($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
