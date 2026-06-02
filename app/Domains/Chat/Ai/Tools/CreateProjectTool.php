<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\Contracts\PresentsToolCard;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\FindLatestUserMessageImagesService;
use App\Domains\Projects\Services\CreateProjectService;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class CreateProjectTool implements AiTool, PresentsToolCard
{
    /**
     * Sensible defaults seeded for chat-created projects so they are immediately
     * usable by `create_task`. The LLM does not pick these — the user confirmed
     * we want a fixed sensible kanban set.
     *
     * @var list<array{category:string,name:string}>
     */
    private const DEFAULT_STATUSES = [
        ['category' => 'not_started', 'name' => 'Zu erledigen'],
        ['category' => 'active', 'name' => 'In Bearbeitung'],
        ['category' => 'done', 'name' => 'Erledigt'],
    ];

    public function __construct(
        private FindTeamForUserService $findTeamForUser,
        private CreateProjectService $createProject,
        private FindLatestUserMessageImagesService $latestImages,
        private ListTeamsForUserService $listTeamsForUser,
    ) {}

    public function name(): string
    {
        return 'create_project';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Create a project in a team the user is a member of. Only call when the team is unambiguous. '
            .'`name` MUST be written in German — a concise project title, no prefixes, no emojis. '
            .'Sensible default statuses are seeded automatically; do not ask about them. '
            .'See the system rules for the exact contract.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'maxLength' => 80],
                'description' => ['type' => 'string', 'maxLength' => 2000],
            ],
            'required' => ['team_id', 'name'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $teamId = $arguments['team_id'] ?? null;
        if (! is_int($teamId) && ! (is_string($teamId) && ctype_digit($teamId))) {
            return AiToolValidationResult::fail(['team_id is required and must be an integer.']);
        }
        $teamId = (int) $teamId;

        $team = $this->findTeamForUser->execute($user, $teamId);
        if ($team === null) {
            return AiToolValidationResult::fail(['You do not have access to that team.']);
        }

        $errors = [];

        $name = $arguments['name'] ?? null;
        if (! is_string($name) || trim($name) === '') {
            $errors[] = 'name is required.';
        } elseif (mb_strlen(trim($name)) > 80) {
            $errors[] = 'name must be 80 characters or less.';
        }

        $description = $arguments['description'] ?? null;
        if ($description !== null) {
            if (! is_string($description)) {
                $errors[] = 'description must be a string.';
                $description = null;
            } elseif (mb_strlen($description) > 2000) {
                $errors[] = 'description must be 2000 characters or less.';
            }
        }

        if ($errors !== []) {
            return AiToolValidationResult::fail($errors);
        }

        return AiToolValidationResult::ok([
            'team_id' => $teamId,
            'name' => trim((string) $name),
            'description' => $description,
            'logo_attachment' => $this->resolveLogoAttachment($arguments, $chat),
        ]);
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

    public function execute(User $user, array $arguments): array
    {
        $teamId = (int) $arguments['team_id'];
        $team = $this->findTeamForUser->execute($user, $teamId);
        if ($team === null) {
            throw (new ModelNotFoundException)->setModel(Team::class, [$teamId]);
        }

        $logo = $this->logoFileFromSnapshot($arguments['logo_attachment'] ?? null);

        $project = $this->createProject->execute(
            $user,
            $team,
            [
                'name' => $arguments['name'],
                'description' => $arguments['description'] ?? null,
            ],
            $logo,
            self::DEFAULT_STATUSES,
        );

        return [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ];
    }

    /**
     * Pull the first image attachment from the most recent user message that
     * carries any — the message that prompted the LLM to emit this tool call.
     * The project logo is singular, so we take only the first image. Attachments
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
     * can be fed into CreateProjectService without changing the service's
     * `?UploadedFile` contract. The `test: true` flag bypasses Symfony's
     * "must have come from an HTTP upload" check, which is correct here:
     * the file lives on the public disk because the user uploaded it via
     * the chat composer, and we just want to copy it to project-logos/.
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

    /**
     * Surface the seeded statuses to the preview-card presenter without making
     * the constant public — keeps the contract testable in one place.
     *
     * @return list<array{category:string,name:string}>
     */
    public static function defaultStatuses(): array
    {
        return self::DEFAULT_STATUSES;
    }

    public function presentCard(array $toolCall, User $user): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];

        $view = [
            'name' => 'create_project',
            'status' => (string) ($toolCall['status'] ?? 'pending'),
            'project_name' => (string) ($args['name'] ?? ''),
            'description' => (string) ($args['description'] ?? ''),
            'team' => null,
            'team_id' => $this->intOrNull($args['team_id'] ?? null),
            'logo' => null,
            'default_statuses' => array_map(
                static fn (array $s): string => $s['name'],
                self::defaultStatuses(),
            ),
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'created_project_id' => null,
            'available_teams' => $this->listTeamsForUser
                ->execute($user)
                ->map(fn (Team $t) => ['id' => $t->id, 'name' => $t->name])
                ->values()
                ->all(),
        ];

        $teamId = $this->intOrNull($args['team_id'] ?? null);
        if ($teamId !== null) {
            $team = $this->findTeamForUser->execute($user, $teamId);
            if ($team !== null) {
                $view['team'] = ['id' => $team->id, 'name' => $team->name];
            }
        }

        $logo = $args['logo_attachment'] ?? null;
        if (is_array($logo) && is_string($logo['url'] ?? null) && $logo['url'] !== '') {
            $view['logo'] = [
                'url' => $logo['url'],
                'name' => is_string($logo['name'] ?? null) ? $logo['name'] : null,
            ];
        }

        $result = $toolCall['result'] ?? null;
        if (is_array($result) && isset($result['project_id'])) {
            $view['created_project_id'] = (int) $result['project_id'];
        }

        return $view;
    }

    private function intOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }
}
