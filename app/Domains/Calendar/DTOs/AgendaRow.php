<?php

namespace App\Domains\Calendar\DTOs;

use App\Domains\Calendar\Enums\AgendaKind;
use Carbon\CarbonImmutable;

final readonly class AgendaRow
{
    public function __construct(
        public AgendaKind $kind,
        public string $timeLabel,
        public string $kindLabel,
        public string $title,
        public string $accent,
        public ?string $href,
        public CarbonImmutable $sortAt,
    ) {}
}
