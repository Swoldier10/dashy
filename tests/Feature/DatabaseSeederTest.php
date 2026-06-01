<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_gives_every_user_a_personal_team(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, User::doesntHave('teams')->count());

        $test = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($test->teams()->where('personal_team', true)->exists());
    }

    public function test_seeding_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::where('email', 'test@example.com')->count());
        $this->assertSame(0, User::doesntHave('teams')->count());
    }
}
