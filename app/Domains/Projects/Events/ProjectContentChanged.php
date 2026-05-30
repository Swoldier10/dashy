<?php

namespace App\Domains\Projects\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * A project's searchable content changed ($deleted = false) or the project was
 * deleted ($deleted = true). Emitted by the Projects domain; Search listens to
 * keep its embedding index in sync.
 */
final class ProjectContentChanged
{
    use Dispatchable;

    public function __construct(
        public int $projectId,
        public bool $deleted = false,
    ) {}
}
