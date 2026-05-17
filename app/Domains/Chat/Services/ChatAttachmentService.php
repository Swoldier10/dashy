<?php

namespace App\Domains\Chat\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

final class ChatAttachmentService
{
    private const AUDIO_MIMES = [
        'audio/webm',
        'audio/ogg',
        'audio/opus',
        'audio/mpeg',
        'audio/mp3',
        'audio/mp4',
        'audio/x-m4a',
        'audio/m4a',
        // PHP's finfo reads the WebM/MP4 container and reports a video/* MIME
        // even for audio-only recordings produced by MediaRecorder. Accepting
        // them here keeps in-browser voice recordings working.
        'video/webm',
        'video/mp4',
    ];

    public function __construct(
        private AudioTranscriptionService $transcriber,
    ) {}

    /**
     * @return array{type: string, path: string, url: string, name: string, mime: string, size: int, transcript: ?string}
     */
    public function storeImage(User $owner, ?int $chatId, UploadedFile $file): array
    {
        Validator::make(['image' => $file], [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ])->validate();

        $path = $file->storePublicly($this->scopeFor($owner, $chatId), 'public');

        return [
            'type' => 'image',
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'name' => $file->getClientOriginalName() ?: basename($path),
            'mime' => $file->getMimeType() ?: 'image/png',
            'size' => $file->getSize() ?: 0,
            'transcript' => null,
        ];
    }

    /**
     * @return array{type: string, path: string, url: string, name: string, mime: string, size: int, transcript: ?string}
     */
    public function storeAudio(User $owner, ?int $chatId, UploadedFile $file): array
    {
        Validator::make(['audio' => $file], [
            'audio' => [
                'required',
                'file',
                'mimetypes:'.implode(',', self::AUDIO_MIMES),
                'max:20480',
            ],
        ])->validate();

        $path = $file->storePublicly($this->scopeFor($owner, $chatId), 'public');
        $originalName = $file->getClientOriginalName() ?: basename($path);

        return [
            'type' => 'audio',
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'name' => $originalName,
            'mime' => $file->getMimeType() ?: 'audio/webm',
            'size' => $file->getSize() ?: 0,
            'duration_seconds' => $this->parseDurationFromName($originalName),
            'transcript' => $this->transcriber->transcribe($path),
        ];
    }

    /**
     * The composer encodes the recorded length as `voice-{centiseconds}cs.{ext}`
     * so the browser can render a correct timer next to the audio bubble even
     * though MediaRecorder's WebM container reports duration = Infinity. Returns
     * null when the filename doesn't follow this convention (e.g. user dragged
     * an arbitrary audio file in).
     */
    private function parseDurationFromName(string $filename): ?float
    {
        if (preg_match('/-(\d+)cs\.[a-z0-9]+$/i', $filename, $matches) === 1) {
            return ((int) $matches[1]) / 100;
        }

        return null;
    }

    private function scopeFor(User $owner, ?int $chatId): string
    {
        $bucket = $chatId === null ? '_pending' : (string) $chatId;

        return "chat-attachments/{$owner->id}/{$bucket}";
    }

    /**
     * Format `12.4` → `0:12`, `73` → `1:13`. Used by the chat templates to
     * render a reliable duration label next to the audio player.
     */
    public static function formatDuration(int|float $seconds): string
    {
        $total = max(0, (int) round($seconds));
        $minutes = intdiv($total, 60);
        $rem = $total % 60;

        return sprintf('%d:%02d', $minutes, $rem);
    }
}
