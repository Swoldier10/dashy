<?php

namespace App\Domains\Codex\Services;

use App\Domains\Codex\Actions\CreateCodexConnectionAction;
use App\Domains\Codex\Actions\DeleteCodexConnectionAction;
use App\Domains\Codex\Actions\FindCodexConnectionForUserAction;
use App\Domains\Codex\Actions\UpdateCodexConnectionAction;
use App\Domains\Codex\DTOs\CodexDeviceCode;
use App\Domains\Codex\DTOs\CodexTokenSet;
use App\Domains\Codex\Exceptions\CodexAuthException;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * OpenAI Codex device-code OAuth.
 *
 * Web apps cannot use the redirect-based authorisation-code flow against
 * OpenAI's auth server: the registered redirect URIs for the Codex CLI
 * client_id are all on localhost. The device-code flow is the only path that
 * works for a server-deployed client. Constants below mirror the official
 * Codex CLI (and OpenClaw) — they are public-knowledge first-party values,
 * not application secrets.
 */
final class CodexAuthService
{
    private const AUTH_BASE_URL = 'https://auth.openai.com';

    private const CLIENT_ID = 'app_EMoamEEZ73f0CkXaXp7hrann';

    private const REDIRECT_URI = self::AUTH_BASE_URL.'/deviceauth/callback';

    private const VERIFICATION_URL = self::AUTH_BASE_URL.'/codex/device';

    private const ORIGINATOR = 'dashy';

    public function __construct(
        private FindCodexConnectionForUserAction $findConnection,
        private CreateCodexConnectionAction $createConnection,
        private UpdateCodexConnectionAction $updateConnection,
        private DeleteCodexConnectionAction $deleteConnection,
    ) {}

    /**
     * Start a device-code flow. Returns the user-facing code + the opaque
     * device_auth_id we use when polling for completion.
     */
    public function startDeviceCode(): CodexDeviceCode
    {
        try {
            $response = Http::withHeaders($this->jsonHeaders())
                ->asJson()
                ->post(self::AUTH_BASE_URL.'/api/accounts/deviceauth/usercode', [
                    'client_id' => self::CLIENT_ID,
                ])
                ->throw();
        } catch (Throwable $e) {
            report($e);
            throw new CodexAuthException('Could not start the Codex sign-in. Please try again.', 0, $e);
        }

        $body = $response->json();
        $deviceAuthId = is_string($body['device_auth_id'] ?? null) ? $body['device_auth_id'] : null;
        $userCode = is_string($body['user_code'] ?? null) ? $body['user_code'] : (is_string($body['usercode'] ?? null) ? $body['usercode'] : null);
        $intervalSeconds = is_numeric($body['interval'] ?? null) ? max(1, (int) $body['interval']) : 5;

        if ($deviceAuthId === null || $userCode === null) {
            throw new CodexAuthException('Codex did not return a device code.');
        }

        return new CodexDeviceCode(
            deviceAuthId: $deviceAuthId,
            userCode: $userCode,
            verificationUrl: self::VERIFICATION_URL,
            pollIntervalSeconds: $intervalSeconds,
        );
    }

    /**
     * Poll once. Returns null while still pending, throws on hard error,
     * persists + returns the connection on success.
     */
    public function pollForToken(User $user, string $deviceAuthId, string $userCode): ?CodexConnection
    {
        $response = Http::withHeaders($this->jsonHeaders())
            ->asJson()
            ->post(self::AUTH_BASE_URL.'/api/accounts/deviceauth/token', [
                'device_auth_id' => $deviceAuthId,
                'user_code' => $userCode,
            ]);

        if (in_array($response->status(), [403, 404], true)) {
            return null; // user hasn't approved yet
        }

        if ($response->failed()) {
            throw new CodexAuthException('Codex device authorisation failed.');
        }

        $body = $response->json();
        $authCode = is_string($body['authorization_code'] ?? null) ? $body['authorization_code'] : null;
        $verifier = is_string($body['code_verifier'] ?? null) ? $body['code_verifier'] : null;
        if ($authCode === null || $verifier === null) {
            throw new CodexAuthException('Codex authorisation response was incomplete.');
        }

        $tokenSet = $this->exchangeCodeForToken($authCode, $verifier);

        return DB::transaction(function () use ($user, $tokenSet) {
            $existing = $this->findConnection->execute($user);
            if ($existing) {
                return $this->updateConnection->execute($existing, $tokenSet->toAttributes());
            }

            return $this->createConnection->execute(
                ['user_id' => $user->id] + $tokenSet->toAttributes()
            );
        });
    }

    /**
     * Refresh the access token if expired. Called by CodexClient before each
     * API call. If refresh fails (revoked, refresh-token expired, network),
     * the broken connection is deleted and CodexNotConnectedException flips
     * the UI to the "Connect Codex" prompt on the next render.
     */
    public function ensureFreshToken(CodexConnection $connection): CodexConnection
    {
        if (! $connection->isExpired() || $connection->refresh_token === null) {
            return $connection;
        }

        try {
            $response = Http::withHeaders($this->formHeaders())
                ->asForm()
                ->post(self::AUTH_BASE_URL.'/oauth/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $connection->refresh_token,
                    'client_id' => self::CLIENT_ID,
                ])
                ->throw();
        } catch (Throwable $e) {
            report($e);
            DB::transaction(fn () => $this->deleteConnection->execute($connection));
            throw new CodexNotConnectedException('Codex session expired. Please reconnect.', 0, $e);
        }

        $tokenSet = CodexTokenSet::fromTokenResponse($this->arrayBody($response));

        return DB::transaction(fn () => $this->updateConnection->execute($connection, $tokenSet->toAttributes()));
    }

    private function exchangeCodeForToken(string $authorizationCode, string $codeVerifier): CodexTokenSet
    {
        try {
            $response = Http::withHeaders($this->formHeaders())
                ->asForm()
                ->post(self::AUTH_BASE_URL.'/oauth/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $authorizationCode,
                    'redirect_uri' => self::REDIRECT_URI,
                    'client_id' => self::CLIENT_ID,
                    'code_verifier' => $codeVerifier,
                ])
                ->throw();
        } catch (Throwable $e) {
            report($e);
            throw new CodexAuthException('Codex token exchange failed.', 0, $e);
        }

        return CodexTokenSet::fromTokenResponse($this->arrayBody($response));
    }

    /**
     * @return array<string, string>
     */
    private function jsonHeaders(): array
    {
        return [
            'originator' => self::ORIGINATOR,
            'User-Agent' => self::ORIGINATOR,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formHeaders(): array
    {
        return $this->jsonHeaders();
    }

    /**
     * @return array<string, mixed>
     */
    private function arrayBody(Response $response): array
    {
        $body = $response->json();

        return is_array($body) ? $body : [];
    }
}
