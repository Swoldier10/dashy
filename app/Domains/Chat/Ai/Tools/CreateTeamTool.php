<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\FindLatestUserMessageImagesService;
use App\Domains\Teams\Services\CreateTeamService;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * CONFIRM-WRITE. Creates a new team owned by the acting user. Unlike
 * create_project there is no team_id to authorize against — any user may
 * create a team, and CreateTeamService attaches them as Owner. An image
 * attached in the conversation becomes the team logo automatically.
 */
final class CreateTeamTool implements AiTool
{
    public function __construct(
        private CreateTeamService $createTeam,
        private FindLatestUserMessageImagesService $latestImages,
    ) {}

    public function name(): string
    {
        return 'create_team';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Create a new team (workspace) owned by the user. `name` is the team name written '
            .'EXACTLY as the user gave it — it is a proper noun, do NOT translate it. If the user '
            .'attached an image in the conversation it automatically becomes the team logo; do not ask. '
            .'See the system rules for the exact contract.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 80],
            ],
            'required' => ['name'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $name = $arguments['name'] ?? null;
        if (! is_string($name) || trim($name) === '') {
            return AiToolValidationResult::fail(['name is required.']);
        }
        if (mb_strlen(trim($name)) > 80) {
            return AiToolValidationResult::fail(['name must be 80 characters or less.']);
        }

        return AiToolValidationResult::ok([
            'name' => trim($name),
            'logo_attachment' => $this->resolveLogoAttachment($arguments, $chat),
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $logo = $this->logoFileFromSnapshot($arguments['logo_attachment'] ?? null);

        $team = $this->createTeam->execute($user, ['name' => $arguments['name']], $logo);

        return [
            'team_id' => $team->id,
            'name' => (string) $team->name,
        ];
    }

    /**
     * Preserve an already-snapshotted logo across re-validation. The first time
     * validate() runs (on the LLM's emission), `logo_attachment` is absent from
     * the arguments so we snapshot from chat. On re-validation after user
     * edits, the key is present (either as a dict or explicit null), so we
     * preserve / clear without touching the chat again.
     *
     * @param  array<string, mixed>  $arguments
     * @return array{path:string,url:string,mime:?string,name:?string}|null
     */
    private function resolveLogoAttachment(array $arguments, ?Chat $chat): ?array
    {
        if (array_key_exists('logo_attachment', $arguments)) {
            $existing = $arguments['logo_attachment'];
            if (! is_array($existing)) {
                return null;
            }
            $path = $existing['path'] ?? null;
            $url = $existing['url'] ?? null;
            if (! is_string($path) || ! is_string($url)) {
                return null;
            }

            return [
                'path' => $path,
                'url' => $url,
                'mime' => is_string($existing['mime'] ?? null) ? $existing['mime'] : null,
                'name' => is_string($existing['name'] ?? null) ? $existing['name'] : null,
            ];
        }

        return $this->snapshotLogoFromChat($chat);
    }

    /**
     * Pull the first image attachment from the most recent user message that
     * carries any — the message that prompted the LLM to emit this tool call.
     * The team logo is singular, so we take only the first image. Attachments
     * are snapshotted at validation time so they survive intermediate text-only
     * user messages (a disambiguation reply, or a message between preview and
     * confirm).
     *
     * @return array{path:string, url:string, mime:?string, name:?string}|null
     */
    private function snapshotLogoFromChat(?Chat $chat): ?array
    {
        if ($chat === null) {
            return null;
        }

        // The first image attached to the latest user message becomes the logo.
        // The DB read lives in a Chat-domain service (no Eloquent in the tool).
        return $this->latestImages->execute($chat)[0] ?? null;
    }

    /**
     * Wrap an already-stored chat attachment as a test-mode UploadedFile so it
     * can be fed into CreateTeamService without changing the service's
     * `?UploadedFile` contract. The `test: true` flag bypasses Symfony's
     * "must have come from an HTTP upload" check, which is correct here:
     * the file lives on the public disk because the user uploaded it via
     * the chat composer, and we just want to copy it to team-logos/.
     *
     * @param  array<string, mixed>|null  $snapshot
     */
    private function logoFileFromSnapshot(mixed $snapshot): ?UploadedFile
    {
        if (! is_array($snapshot)) {
            return null;
        }
        $path = $snapshot['path'] ?? null;
        if (! is_string($path) || $path === '') {
            return null;
        }
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($path);
        $name = is_string($snapshot['name'] ?? null) ? $snapshot['name'] : basename($path);
        $mime = is_string($snapshot['mime'] ?? null) ? $snapshot['mime'] : null;

        return new UploadedFile($absolutePath, $name, $mime, null, true);
    }
}
