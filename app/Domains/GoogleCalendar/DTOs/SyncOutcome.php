<?php

namespace App\Domains\GoogleCalendar\DTOs;

final class SyncOutcome
{
    public function __construct(
        public int $pulled = 0,
        public int $pushed = 0,
        public int $deletedRemote = 0,
        public int $deletedLocal = 0,
        public int $skipped = 0,
        public int $errors = 0,
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'pulled' => $this->pulled,
            'pushed' => $this->pushed,
            'deleted_remote' => $this->deletedRemote,
            'deleted_local' => $this->deletedLocal,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
