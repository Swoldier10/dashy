<?php

namespace Tests\Unit\Support\Concerns;

use App\Support\Concerns\DetectsUniqueConstraintViolations;
use Exception;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class DetectsUniqueConstraintViolationsTest extends TestCase
{
    private function detector(): object
    {
        return new class
        {
            use DetectsUniqueConstraintViolations;

            public function check(QueryException $e): bool
            {
                return $this->isUniqueViolation($e);
            }
        };
    }

    private function queryException(string $sqlState): QueryException
    {
        $previous = new Exception('constraint failed', 0);
        $exception = new QueryException('sqlite', 'insert into t', [], $previous);
        $exception->errorInfo = [$sqlState, 19, 'constraint failed'];

        return $exception;
    }

    public function test_detects_sqlstate_23000_via_error_info(): void
    {
        $this->assertTrue($this->detector()->check($this->queryException('23000')));
    }

    public function test_rejects_other_sqlstates(): void
    {
        $this->assertFalse($this->detector()->check($this->queryException('42S02')));
    }
}
