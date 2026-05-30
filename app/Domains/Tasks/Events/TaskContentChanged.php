<?php

namespace App\Domains\Tasks\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * A task's searchable content was created/updated ($deleted = false) or the
 * task was deleted ($deleted = true). Emitted by the Tasks domain; the Search
 * domain listens to keep the embedding index in sync. Tasks knows nothing
 * about Search — the dependency points consumer → producer.
 */
final class TaskContentChanged
{
    use Dispatchable;

    public function __construct(
        public int $taskId,
        public bool $deleted = false,
    ) {}
}
