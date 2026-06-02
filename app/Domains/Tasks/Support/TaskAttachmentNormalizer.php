<?php

namespace App\Domains\Tasks\Support;

/**
 * Normalises raw image-attachment input into the canonical shape stored in the
 * `tasks.attachments` JSON column. Shared by every write path that persists
 * task attachments (create + append) so the stored shape can never drift.
 *
 * Drops entries missing a usable `path`/`url`; defaults `mime` to `image/png`
 * and `name` to the file's basename; stamps `type` as `image`.
 */
final class TaskAttachmentNormalizer
{
    /**
     * @param  array<int, array<string, mixed>>  $attachments
     * @return array<int, array{type: string, path: string, url: string, mime: string, name: string}>
     */
    public static function normalise(array $attachments): array
    {
        return array_values(array_filter(array_map(static function (array $att): ?array {
            $path = $att['path'] ?? null;
            $url = $att['url'] ?? null;
            if (! is_string($path) || $path === '' || ! is_string($url) || $url === '') {
                return null;
            }

            return [
                'type' => 'image',
                'path' => $path,
                'url' => $url,
                'mime' => is_string($att['mime'] ?? null) ? $att['mime'] : 'image/png',
                'name' => is_string($att['name'] ?? null) ? $att['name'] : basename($path),
            ];
        }, $attachments)));
    }
}
