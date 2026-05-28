<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper over the Google Calendar v3 events endpoints. Each method
 * returns the raw Response so callers can branch on status codes (412
 * preconditions, 410 sync-token-gone, etc.) without re-implementing HTTP
 * concerns.
 */
final class GoogleCalendarApiClient
{
    private const BASE_URL = 'https://www.googleapis.com/calendar/v3/calendars';

    /**
     * @param  array<string, mixed>  $params
     */
    public function listEvents(GoogleCalendarConnection $connection, array $params): Response
    {
        return $this->client($connection)
            ->get(self::BASE_URL.'/'.rawurlencode($connection->calendar_id).'/events', $params);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function insertEvent(GoogleCalendarConnection $connection, array $payload): Response
    {
        return $this->client($connection)
            ->post(self::BASE_URL.'/'.rawurlencode($connection->calendar_id).'/events', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function patchEvent(
        GoogleCalendarConnection $connection,
        string $googleEventId,
        array $payload,
        ?string $etag = null,
    ): Response {
        $client = $this->client($connection);
        if ($etag !== null) {
            $client = $client->withHeaders(['If-Match' => $etag]);
        }

        return $client->patch(
            self::BASE_URL.'/'.rawurlencode($connection->calendar_id).'/events/'.rawurlencode($googleEventId),
            $payload,
        );
    }

    public function deleteEvent(GoogleCalendarConnection $connection, string $googleEventId): Response
    {
        return $this->client($connection)
            ->delete(self::BASE_URL.'/'.rawurlencode($connection->calendar_id).'/events/'.rawurlencode($googleEventId));
    }

    private function client(GoogleCalendarConnection $connection): PendingRequest
    {
        return Http::withToken($connection->access_token)
            ->acceptJson()
            ->asJson()
            ->retry(2, sleepMilliseconds: 250, throw: false);
    }
}
