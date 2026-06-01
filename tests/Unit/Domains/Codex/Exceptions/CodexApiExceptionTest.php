<?php

namespace Tests\Unit\Domains\Codex\Exceptions;

use App\Domains\Codex\Exceptions\CodexApiException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Tests\TestCase;

class CodexApiExceptionTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, string>  $headers
     */
    private function response(int $status, array $body, array $headers = []): Response
    {
        return new Response(new Psr7Response($status, $headers, json_encode($body)));
    }

    public function test_from_response_parses_insufficient_quota_as_out_of_credits(): void
    {
        $e = CodexApiException::fromResponse($this->response(429, [
            'error' => ['type' => 'insufficient_quota', 'message' => 'You exceeded your current quota'],
        ]));

        $this->assertSame(429, $e->status);
        $this->assertSame('insufficient_quota', $e->errorType);
        $this->assertTrue($e->isOutOfCredits());
        $this->assertStringContainsString('credits', strtolower($e->userMessage()));
    }

    public function test_from_response_parses_rate_limit_with_retry_after(): void
    {
        $e = CodexApiException::fromResponse($this->response(429, [
            'error' => ['type' => 'rate_limit_exceeded', 'message' => 'slow down'],
        ], ['Retry-After' => '30']));

        $this->assertSame(429, $e->status);
        $this->assertFalse($e->isOutOfCredits());
        $this->assertSame(30, $e->retryAfterSeconds);
        $this->assertStringContainsString('30', $e->userMessage());
    }

    public function test_from_response_flags_auth_failure(): void
    {
        $e = CodexApiException::fromResponse($this->response(401, [
            'error' => ['type' => 'invalid_request_error', 'message' => 'bad token'],
        ]));

        $this->assertTrue($e->isAuthFailure());
        $this->assertStringContainsString('reconnect', strtolower($e->userMessage()));
    }

    public function test_billing_statuses_map_to_plan_message(): void
    {
        foreach ([402, 403] as $status) {
            $e = CodexApiException::fromResponse($this->response($status, ['error' => ['message' => 'no']]));
            $this->assertStringContainsString('plan', strtolower($e->userMessage()));
        }
    }

    public function test_server_error_maps_to_unavailable(): void
    {
        $e = CodexApiException::fromResponse($this->response(503, []));

        $this->assertStringContainsString('unavailable', strtolower($e->userMessage()));
    }

    public function test_context_length_maps_to_too_long(): void
    {
        $e = CodexApiException::fromResponse($this->response(400, [
            'error' => ['code' => 'context_length_exceeded', 'message' => 'too long'],
        ]));

        $this->assertSame('context_length_exceeded', $e->errorType);
        $this->assertStringContainsString('too long', strtolower($e->userMessage()));
    }

    public function test_connection_failed_factory(): void
    {
        $e = CodexApiException::connectionFailed(new ConnectionException('refused'));

        $this->assertTrue($e->isConnectionError);
        $this->assertNull($e->status);
        $this->assertStringContainsString('connection', strtolower($e->userMessage()));
    }

    public function test_stream_truncated_factory(): void
    {
        $e = CodexApiException::streamTruncated();

        $this->assertTrue($e->isStreamTruncated);
        $this->assertStringContainsString('cut off', strtolower($e->userMessage()));
    }

    public function test_unknown_status_falls_back_to_generic_message(): void
    {
        $e = CodexApiException::fromResponse($this->response(418, ['error' => ['message' => 'teapot']]));

        $this->assertStringContainsString('codex', strtolower($e->userMessage()));
    }
}
