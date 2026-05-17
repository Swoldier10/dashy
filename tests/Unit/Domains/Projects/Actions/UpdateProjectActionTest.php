<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\UpdateProjectAction;
use App\Domains\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_provided_attributes(): void
    {
        $project = Project::factory()->create([
            'name' => 'Old name',
            'description' => 'Old desc',
            'logo' => null,
        ]);

        $updated = (new UpdateProjectAction)->execute($project, [
            'name' => 'New name',
            'description' => 'New desc',
            'logo' => 'https://example.test/new.png',
        ]);

        $this->assertSame('New name', $updated->name);
        $this->assertSame('New desc', $updated->description);
        $this->assertSame('https://example.test/new.png', $updated->logo);
    }

    public function test_only_updates_keys_in_attributes(): void
    {
        $project = Project::factory()->create([
            'name' => 'Stay',
            'description' => 'Stay desc',
            'logo' => 'https://stay.example.test/logo.png',
        ]);

        $updated = (new UpdateProjectAction)->execute($project, [
            'name' => 'Changed',
        ]);

        $this->assertSame('Changed', $updated->name);
        $this->assertSame('Stay desc', $updated->description);
        $this->assertSame('https://stay.example.test/logo.png', $updated->logo);
    }
}
