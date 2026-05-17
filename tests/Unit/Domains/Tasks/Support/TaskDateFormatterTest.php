<?php

namespace Tests\Unit\Domains\Tasks\Support;

use App\Domains\Tasks\Support\TaskDateFormatter;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskDateFormatterTest extends TestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        // Wednesday, 2026-05-20 12:00:00 local — pin for deterministic week math.
        $this->now = Carbon::create(2026, 5, 20, 12, 0, 0);
    }

    public function test_null_renders_em_dash(): void
    {
        $this->assertSame('—', TaskDateFormatter::format(null, $this->now));
    }

    public function test_today(): void
    {
        $date = Carbon::create(2026, 5, 20, 0, 0, 0);
        $this->assertSame('Today', TaskDateFormatter::format($date, $this->now));
    }

    public function test_tomorrow(): void
    {
        $date = Carbon::create(2026, 5, 21, 0, 0, 0);
        $this->assertSame('Tomorrow', TaskDateFormatter::format($date, $this->now));
    }

    public function test_yesterday(): void
    {
        $date = Carbon::create(2026, 5, 19, 0, 0, 0);
        $this->assertSame('Yesterday', TaskDateFormatter::format($date, $this->now));
    }

    public function test_this_week_short_dow(): void
    {
        // 2026-05-24 is the Sunday of the week containing 2026-05-20 (Wed)
        $date = Carbon::create(2026, 5, 24, 0, 0, 0);
        $this->assertSame('Sun', TaskDateFormatter::format($date, $this->now));
    }

    public function test_this_week_with_time(): void
    {
        $date = Carbon::create(2026, 5, 24, 9, 0, 0);
        $this->assertSame('Sun 9am', TaskDateFormatter::format($date, $this->now));

        $date = Carbon::create(2026, 5, 22, 13, 30, 0);
        $this->assertSame('Fri 1:30pm', TaskDateFormatter::format($date, $this->now));
    }

    public function test_same_year_uses_month_day(): void
    {
        $date = Carbon::create(2026, 7, 4, 0, 0, 0);
        $this->assertSame('Jul 4', TaskDateFormatter::format($date, $this->now));
    }

    public function test_other_year_uses_iso(): void
    {
        $date = Carbon::create(2027, 3, 4, 0, 0, 0);
        $this->assertSame('2027-03-04', TaskDateFormatter::format($date, $this->now));
    }
}
