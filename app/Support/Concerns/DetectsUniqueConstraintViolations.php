<?php

namespace App\Support\Concerns;

use Illuminate\Database\QueryException;

/**
 * Shared detection of SQLSTATE 23000 (integrity/unique constraint
 * violations) for insert-or-ignore and treat-as-already-done flows.
 */
trait DetectsUniqueConstraintViolations
{
    protected function isUniqueViolation(QueryException $e): bool
    {
        return $e->getCode() === '23000' || ($e->errorInfo[0] ?? null) === '23000';
    }
}
