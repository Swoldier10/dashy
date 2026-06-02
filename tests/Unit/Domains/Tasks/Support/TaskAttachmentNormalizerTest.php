<?php

namespace Tests\Unit\Domains\Tasks\Support;

use App\Domains\Tasks\Support\TaskAttachmentNormalizer;
use Tests\TestCase;

class TaskAttachmentNormalizerTest extends TestCase
{
    public function test_stamps_type_and_keeps_path_and_url(): void
    {
        $out = TaskAttachmentNormalizer::normalise([
            ['path' => 'a/img.png', 'url' => 'https://t/a/img.png', 'mime' => 'image/jpeg', 'name' => 'img.png'],
        ]);

        $this->assertSame([
            ['type' => 'image', 'path' => 'a/img.png', 'url' => 'https://t/a/img.png', 'mime' => 'image/jpeg', 'name' => 'img.png'],
        ], $out);
    }

    public function test_defaults_mime_and_name(): void
    {
        $out = TaskAttachmentNormalizer::normalise([
            ['path' => 'bucket/sub/photo.webp', 'url' => 'https://t/photo.webp'],
        ]);

        $this->assertSame('image/png', $out[0]['mime']);
        $this->assertSame('photo.webp', $out[0]['name']);
    }

    public function test_drops_entries_missing_path_or_url(): void
    {
        $out = TaskAttachmentNormalizer::normalise([
            ['url' => 'https://t/no-path.png'],
            ['path' => 'a/no-url.png'],
            ['path' => '', 'url' => 'https://t/empty.png'],
            ['path' => 'a/ok.png', 'url' => 'https://t/a/ok.png'],
        ]);

        $this->assertCount(1, $out);
        $this->assertSame('a/ok.png', $out[0]['path']);
    }

    public function test_reindexes_after_filtering(): void
    {
        $out = TaskAttachmentNormalizer::normalise([
            ['path' => 'a/skip.png'], // dropped (no url)
            ['path' => 'a/keep.png', 'url' => 'https://t/a/keep.png'],
        ]);

        $this->assertArrayHasKey(0, $out);
        $this->assertSame('a/keep.png', $out[0]['path']);
    }
}
