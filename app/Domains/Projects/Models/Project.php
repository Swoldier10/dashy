<?php

namespace App\Domains\Projects\Models;

use App\Domains\Projects\Observers\ProjectSearchObserver;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Models\Team;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy([ProjectSearchObserver::class])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = ['team_id', 'name', 'description', 'logo'];

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(ProjectStatus::class)
            ->orderBy('category')
            ->orderBy('position');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function initials(): string
    {
        return Str::of((string) $this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
