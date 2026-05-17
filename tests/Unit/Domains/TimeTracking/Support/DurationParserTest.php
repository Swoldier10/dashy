<?php

namespace Tests\Unit\Domains\TimeTracking\Support;

use App\Domains\TimeTracking\Support\DurationParser;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DurationParserTest extends TestCase
{
    /**
     * @return iterable<array{0: string, 1: int}>
     */
    public static function validInputs(): iterable
    {
        return [
            ['3h 20m', 3 * 3600 + 20 * 60],
            ['3h20m', 3 * 3600 + 20 * 60],
            ['90m', 90 * 60],
            ['1h', 3600],
            ['1.5h', 5400],
            ['45s', 45],
            ['1h 30m 15s', 3600 + 30 * 60 + 15],
            ['2', 120],
            ['2.5', 150],
            ['1:30', 5400],
            ['1:30:00', 5400],
            ['0:45:30', 45 * 60 + 30],
            ['   2h   ', 7200],
            ['2H', 7200],
        ];
    }

    #[DataProvider('validInputs')]
    public function test_parses_valid_durations(string $input, int $expected): void
    {
        $this->assertSame($expected, DurationParser::parse($input));
    }

    /**
     * @return iterable<array{0: string}>
     */
    public static function invalidInputs(): iterable
    {
        return [
            [''],
            ['asdf'],
            ['3z'],
            ['-5m'],
            ['h'],
            ['m'],
            ['s'],
            ['0'],
            ['0h'],
            ['0m'],
            ['0:00'],
            ['9999h'],
        ];
    }

    #[DataProvider('invalidInputs')]
    public function test_rejects_invalid_durations(string $input): void
    {
        $this->expectException(ValidationException::class);
        DurationParser::parse($input);
    }

    public function test_format_returns_combined_units(): void
    {
        $this->assertSame('2h 15m', DurationParser::format(2 * 3600 + 15 * 60));
    }

    public function test_format_returns_hours_when_no_minute_remainder(): void
    {
        $this->assertSame('3h', DurationParser::format(3 * 3600));
    }

    public function test_format_returns_minutes_when_under_one_hour(): void
    {
        $this->assertSame('45m', DurationParser::format(45 * 60));
    }

    public function test_format_returns_seconds_when_under_one_minute(): void
    {
        $this->assertSame('30s', DurationParser::format(30));
    }

    public function test_format_returns_zero_marker_for_non_positive_values(): void
    {
        $this->assertSame('0m', DurationParser::format(0));
        $this->assertSame('0m', DurationParser::format(-5));
    }

    public function test_format_clock_pads_hours_minutes_seconds(): void
    {
        $this->assertSame('00:00:05', DurationParser::formatClock(5));
        $this->assertSame('00:01:00', DurationParser::formatClock(60));
        $this->assertSame('01:23:45', DurationParser::formatClock(3600 + 23 * 60 + 45));
    }
}
