<?php

namespace App\Providers;

use App\Domains\Chat\Events\MessageContentChanged;
use App\Domains\Projects\Events\ProjectContentChanged;
use App\Domains\Search\Listeners\IndexChangedSource;
use App\Domains\Tasks\Events\TaskContentChanged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the Search domain to the owning domains' content-change events. The
 * owning domains observe their own models (via #[ObservedBy]) and emit these
 * events; Search merely subscribes — it no longer observes foreign models, so
 * the dependency points consumer (Search) → producer (Tasks/Projects/Chat).
 */
class SearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(TaskContentChanged::class, IndexChangedSource::class);
        Event::listen(ProjectContentChanged::class, IndexChangedSource::class);
        Event::listen(MessageContentChanged::class, IndexChangedSource::class);
    }
}
