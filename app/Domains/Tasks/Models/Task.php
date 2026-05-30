<?php

namespace App\Domains\Tasks\Models;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Observers\TaskSearchObserver;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TaskSearchObserver::class])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_status_id',
        'created_by_user_id',
        'name',
        'description',
        'priority',
        'start_date',
        'end_date',
        'position',
        'attachments',
        'is_archived',
    ];

    protected $casts = [
        'priority' => TaskPriority::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'position' => 'integer',
        'attachments' => 'array',
        'is_archived' => 'boolean',
    ];

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }
}
