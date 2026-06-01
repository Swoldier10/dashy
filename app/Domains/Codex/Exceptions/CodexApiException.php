<?php

namespace App\Domains\Codex\Exceptions;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Raised when a Codex (OpenAI Responses API) request fails — either with an
 * HTTP error status, a network/connection failure, or a stream that ended
 * before the model finished.
 *
 * `getMessage()` carries the technical detail (status + error type + body
 * snippet) for logs; `userMessage()` carries the friendly, localized string
 * the UI shows the user. The structured fields let callers distinguish, e.g.,
 * "out of credits" from "rate limited" from "session expired".
 */
class CodexApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?string $errorType = null,
        public readonly ?int $retryAfterSeconds = null,
        public readonly bool $isConnectionError = false,
        public readonly bool $isStreamTruncated = false,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Build from a failed Responses-API HTTP response, parsing the OpenAI error
     * envelope (`{"error": {"type", "code", "message"}}`) and the Retry-After
     * header. Reads the body exactly once (the streamed response body may not
     * be re-readable).
     */
    public static function fromResponse(Response $response): self
    {
        $status = $response->status();
        $raw = (string) $response->body();
        $decoded = json_decode($raw, true);

        $error = is_array($decoded) && is_array($decoded['error'] ?? null) ? $decoded['error'] : [];
        $type = is_string($error['type'] ?? null) ? $error['type'] : null;
        $code = is_string($error['code'] ?? null) ? $error['code'] : null;
        $apiMessage = is_string($error['message'] ?? null) ? $error['message'] : null;

        return new self(
            message: sprintf(
                'Codex API error %d (%s): %s',
                $status,
                $type ?? $code ?? 'unknown',
                $apiMessage ?? (Str::limit($raw, 200) ?: '(empty body)'),
            ),
            status: $status,
            errorType: $type ?? $code,
            retryAfterSeconds: self::parseRetryAfter($response->header('Retry-After')),
        );
    }

    public static function connectionFailed(Throwable $previous): self
    {
        return new self(
            message: 'Could not reach Codex: '.$previous->getMessage(),
            isConnectionError: true,
            previous: $previous,
        );
    }

    public static function streamTruncated(): self
    {
        return new self(
            message: 'Codex stream ended before completion.',
            isStreamTruncated: true,
        );
    }

    public function isOutOfCredits(): bool
    {
        return $this->status === 429 && $this->errorType === 'insufficient_quota';
    }

    public function isAuthFailure(): bool
    {
        return $this->status === 401;
    }

    /**
     * A friendly, localized, user-facing explanation. `getMessage()` stays the
     * technical detail for logs.
     */
    public function userMessage(): string
    {
        if ($this->isConnectionError) {
            return __("Couldn't reach Codex. Check your internet connection and try again.");
        }

        if ($this->isStreamTruncated) {
            return __('The response was cut off. Please try again.');
        }

        if ($this->isOutOfCredits()) {
            return __("You've run out of OpenAI/Codex credits. Add credits or upgrade your plan to keep using the assistant.");
        }

        return match (true) {
            $this->status === 429 => $this->retryAfterSeconds !== null
                ? __('Codex is receiving too many requests. Please wait :seconds seconds and try again.', ['seconds' => $this->retryAfterSeconds])
                : __('Codex is receiving too many requests. Please wait a few seconds and try again.'),
            $this->status === 401 => __('Your Codex session expired. Please reconnect Codex.'),
            in_array($this->status, [402, 403], true) => __('Your OpenAI/Codex plan or billing needs attention. Check your OpenAI account.'),
            $this->status === 400 && $this->errorType === 'context_length_exceeded' => __('This conversation is too long for the model. Start a new chat.'),
            $this->status === 400 && in_array($this->errorType, ['content_policy_violation', 'content_filter', 'invalid_prompt'], true) => __('Codex declined to respond to that request.'),
            $this->status !== null && $this->status >= 500 => __('Codex is temporarily unavailable. Please try again shortly.'),
            default => __("Couldn't complete the request to Codex. Please try again."),
        };
    }

    private static function parseRetryAfter(?string $header): ?int
    {
        if ($header === null || $header === '') {
            return null;
        }

        return is_numeric($header) ? max(0, (int) $header) : null;
    }
}
