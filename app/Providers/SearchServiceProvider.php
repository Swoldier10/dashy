<?php

namespace App\Providers;

use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Models\Project;
use App\Domains\Search\Observers\EmbeddingObserver;
use App\Domains\Tasks\Models\Task;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Task::observe(EmbeddingObserver::class);
        Project::observe(EmbeddingObserver::class);
        Message::observe(EmbeddingObserver::class);
    }
}
