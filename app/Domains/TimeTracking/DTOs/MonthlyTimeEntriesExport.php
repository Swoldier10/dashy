<?php

namespace App\Domains\TimeTracking\DTOs;

final readonly class MonthlyTimeEntriesExport
{
    public function __construct(
        public string $filename,
        public string $contents,
    ) {}
}
