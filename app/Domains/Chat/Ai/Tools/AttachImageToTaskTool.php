<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\FindLatestUserMessageImagesService;
use App\Domains\Tasks\Services\AddTaskAttachmentsService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * CONFIRM-WRITE. Attaches the image(s) the user shared in the conversation to
 * an existing task. The model supplies only the task id — the images are
 * snapshotted server-side from the chat (the model never sees or passes file
 * paths), mirroring create_task / create_project. The user confirms a compact
 * card before anything is written.
 */
final class AttachImageToTaskTool implements AiTool
{
    public function __construct(
        private AddTaskAttachmentsService $addAttachments,
        private FindLatestUserMessageImagesService $latestImages,
        private FindTaskService $findTask,
    ) {}

    public function name(): string
    {
        return 'attach_image_to_task';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Attach the image(s) the user shared in this conversation to an existing task. '
            .'Provide the task id; the images are taken from the conversation automatically — '
            .'you cannot and need not pass file paths. Use this when the user asks to add or '
            .'attach an image or screenshot to a task that already exists.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer'],
            ],
            'required' => ['task_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $taskId = $arguments['task_id'] ?? null;
        if (! is_int($taskId) && ! (is_string($taskId) && ctype_digit($taskId))) {
            return AiToolValidationResult::fail(['task_id is required and must be an integer.']);
        }
        $taskId = (int) $taskId;

        $images = $this->resolveImageAttachments($arguments, $chat);
        if ($images === []) {
            return AiToolValidationResult::fail(['No image found in this conversation to attach.']);
        }

        try {
            $this->findTask->execute($user, $taskId);
        } catch (ModelNotFoundException|AuthorizationException) {
            return AiToolValidationResult::fail(['You do not have access to that task.']);
        }

        return AiToolValidationResult::ok([
            'task_id' => $taskId,
            'image_attachments' => $images,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $result = $this->addAttachments->execute(
            $user,
            (int) $arguments['task_id'],
            $arguments['image_attachments'] ?? [],
        );

        return [
            'task_id' => $result['task_id'],
            'attached_count' => $result['attached_count'],
        ];
    }

    /**
     * Preserve already-snapshotted images across re-validation. Mirrors
     * CreateTaskTool::resolveImageAttachments — on the LLM's first validation
     * `image_attachments` is absent and we snapshot from chat; on re-validation
     * after the card is built the key is present and we keep what's there so the
     * confirmed images stay deterministic.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<int, array{path:string,url:string,mime:?string,name:?string}>
     */
    private function resolveImageAttachments(array $arguments, ?Chat $chat): array
    {
        if (array_key_exists('image_attachments', $arguments)) {
            $existing = $arguments['image_attachments'];
            if (! is_array($existing)) {
                return [];
            }

            return collect($existing)
                ->map(function ($att): ?array {
                    if (! is_array($att)) {
                        return null;
                    }
                    $path = $att['path'] ?? null;
                    $url = $att['url'] ?? null;
                    if (! is_string($path) || ! is_string($url)) {
                        return null;
                    }

                    return [
                        'path' => $path,
                        'url' => $url,
                        'mime' => is_string($att['mime'] ?? null) ? $att['mime'] : null,
                        'name' => is_string($att['name'] ?? null) ? $att['name'] : null,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        }

        if ($chat === null) {
            return [];
        }

        return $this->latestImages->execute($chat);
    }
}
