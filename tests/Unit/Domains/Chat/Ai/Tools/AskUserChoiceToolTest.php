<?php

namespace Tests\Unit\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Tools\AskUserChoiceTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AskUserChoiceToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_happy_path_trims_and_normalises_options(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => '  Which team should I use?  ',
            'options' => ['Folienzuschnitt', '  Raul\'s Team  '],
        ]);

        $this->assertTrue($result->valid, implode(', ', $result->errors));
        $this->assertSame('Which team should I use?', $result->normalized['question']);
        $this->assertSame(['Folienzuschnitt', 'Raul\'s Team'], $result->normalized['options']);
    }

    public function test_validate_rejects_missing_question(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'options' => ['A', 'B'],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_empty_question(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => '   ',
            'options' => ['A', 'B'],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_fewer_than_two_options(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => 'Pick one',
            'options' => ['only'],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_more_than_six_options(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => 'Pick one',
            'options' => ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_duplicate_options(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => 'Pick one',
            'options' => ['A', 'A'],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_empty_option_strings(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => 'Pick one',
            'options' => ['A', '   '],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_non_string_option(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => 'Pick one',
            'options' => ['A', 42],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_validate_rejects_option_longer_than_eighty_chars(): void
    {
        $user = User::factory()->create();

        $result = app(AskUserChoiceTool::class)->validate($user, [
            'question' => 'Pick one',
            'options' => ['A', str_repeat('b', 81)],
        ]);

        $this->assertFalse($result->valid);
    }

    public function test_execute_throws_because_resolution_happens_via_answer_choice_service(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);

        app(AskUserChoiceTool::class)->execute($user, [
            'question' => 'Pick one',
            'options' => ['A', 'B'],
        ]);
    }
}
