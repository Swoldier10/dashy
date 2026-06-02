<?php

namespace App\Domains\Tasks\Services;

use App\Domains\Tasks\Actions\FindTaskAction;
use App\Domains\Tasks\Actions\UpdateTaskAction;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Support\TaskAttachmentNormalizer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

/**
 * Append image attachments to an existing task. Distinct from
 * {@see CreateTaskService} (which sets attachments on creation) because the
 * append/dedup/cap semantics differ, and from {@see UpdateTaskService} (whose
 * validator deliberately excludes attachments). Reuses FindTaskAction +
 * UpdateTaskAction for the DB reads/writes.
 */
final class AddTaskAttachmentsService
{
    /**
     * Hard cap on stored attachments per task — matches CreateTaskService.
     */
    private const MAX_ATTACHMENTS = 10;

    public function __construct(
        private FindTaskAction $find,
        private UpdateTaskAction $update,
    ) {}

    /**
     * Append the given images to the task, skipping any whose `path` already
     * exists on the task. A no-op (nothing new after dedup) performs no write.
     *
     * @param  array<int, array<string, mixed>>  $images
     * @return array{task_id: int, attached_count: int, task: Task}
     */
    public function execute(User $actor, int $taskId, array $images): array
    {
        $task = $this->find->execute($taskId);

        Gate::forUser($actor)->authorize('update', $task);

        $validated = Validator::make(['attachments' => $images], [
            'attachments' => ['nullable', 'array', 'max:'.self::MAX_ATTACHMENTS],
            'attachments.*.path' => ['required_with:attachments.*', 'string'],
            'attachments.*.url' => ['required_with:attachments.*', 'string'],
            'attachments.*.mime' => ['nullable', 'string'],
            'attachments.*.name' => ['nullable', 'string'],
        ])->validate();

        $incoming = TaskAttachmentNormalizer::normalise($validated['attachments'] ?? []);

        // Reading the loaded model attribute is modelling, not a query.
        $existing = is_array($task->attachments) ? $task->attachments : [];
        $existingPaths = array_column($existing, 'path');

        $appended = [];
        foreach ($incoming as $attachment) {
            if (! in_array($attachment['path'], $existingPaths, true)) {
                $appended[] = $attachment;
                $existingPaths[] = $attachment['path'];
            }
        }

        // Existing entries are kept first, so anything beyond the cap drops from
        // the newly-appended tail. attached_count reflects what actually lands.
        $merged = array_slice(array_merge($existing, $appended), 0, self::MAX_ATTACHMENTS);
        $attachedCount = max(0, count($merged) - count($existing));

        if ($attachedCount === 0) {
            return ['task_id' => $task->id, 'attached_count' => 0, 'task' => $task];
        }

        $task = DB::transaction(fn (): Task => $this->update->execute($task, ['attachments' => $merged]));

        return ['task_id' => $task->id, 'attached_count' => $attachedCount, 'task' => $task];
    }
}
