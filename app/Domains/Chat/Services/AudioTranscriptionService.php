<?php

namespace App\Domains\Chat\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Transcribe an audio file via an OpenAI-compatible /audio/transcriptions
 * endpoint. Returns null whenever transcription is unavailable or fails —
 * never throws — so audio uploads keep working even if the API key is
 * missing or the upstream is down.
 */
final class AudioTranscriptionService
{
    public function transcribe(string $diskRelativePath, ?string $languageHint = null): ?string
    {
        $apiKey = (string) config('services.openai.api_key');
        if ($apiKey === '') {
            return null;
        }

        $bytes = Storage::disk('public')->get($diskRelativePath);
        if ($bytes === null || $bytes === '') {
            Log::warning('Audio transcription skipped: file unreadable', [
                'path' => $diskRelativePath,
            ]);

            return null;
        }

        $url = (string) config('services.openai.transcription_url', 'https://api.openai.com/v1/audio/transcriptions');
        $model = (string) config('services.openai.transcription_model', 'whisper-1');
        $timeout = (int) config('services.openai.transcription_timeout', 30);
        $filename = basename($diskRelativePath);

        $request = Http::withToken($apiKey)
            ->timeout($timeout)
            ->attach('file', $bytes, $filename)
            ->asMultipart();

        try {
            $response = $request->post($url, array_filter([
                'model' => $model,
                'language' => $languageHint,
            ]));
        } catch (Throwable $e) {
            Log::warning('Audio transcription request failed', [
                'path' => $diskRelativePath,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Audio transcription returned non-2xx', [
                'path' => $diskRelativePath,
                'status' => $response->status(),
                'body' => Str::limit((string) $response->body(), 500),
            ]);

            return null;
        }

        $text = $response->json('text');

        return is_string($text) && trim($text) !== '' ? $text : null;
    }
}
