<?php

namespace App\Domains\Projects\Models;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Tasks\Models\Task;
use Database\Factories\ProjectStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStatus extends Model
{
    /** @use HasFactory<ProjectStatusFactory> */
    use HasFactory;

    protected $fillable = ['project_id', 'category', 'name', 'position'];

    protected $casts = [
        'category' => ProjectStatusCategory::class,
        'position' => 'integer',
    ];

    protected static function newFactory(): ProjectStatusFactory
    {
        return ProjectStatusFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
