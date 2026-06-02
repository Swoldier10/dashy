<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\FindLatestUserMessageAttachmentsAction;
use App\Domains\Chat\Models\Chat;

/**
 * Image attachments carried by the most recent user message that has any —
 * the message that prompted the current tool call. AI write-tools snapshot
 * these at validation time so an attached image survives intermediate user
 * messages (a text-only reply between the upload and the tool call, or a
 * message between preview and confirm).
 */
final class FindLatestUserMessageImagesService
{
    public function __construct(
        private FindLatestUserMessageAttachmentsAction $attachments,
    ) {}

    /**
     * @return array<int, array{path: string, url: string, mime: ?string, name: ?string}>
     */
    public function execute(Chat $chat): array
    {
        return collect($this->attachments->execute($chat))
            ->filter(fn ($att): bool => is_array($att) && ($att['type'] ?? null) === 'image')
            ->map(fn (array $att): array => [
                'path' => $att['path'] ?? null,
                'url' => $att['url'] ?? null,
                'mime' => is_string($att['mime'] ?? null) ? $att['mime'] : null,
                'name' => is_string($att['name'] ?? null) ? $att['name'] : null,
            ])
            ->filter(fn (array $att): bool => is_string($att['path']) && is_string($att['url']))
            ->values()
            ->all();
    }
}
