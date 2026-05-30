<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\CreateEventService;
use App\Domains\Calendar\Services\DeleteEventService;
use App\Domains\Calendar\Services\FindEventService;
use App\Domains\Calendar\Services\UpdateEventService;
use App\Domains\GoogleCalendar\Actions\DeleteGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\Actions\FindLinkByGoogleEventIdAction;
use App\Domains\GoogleCalendar\Actions\UpdateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Actions\UpsertGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\DTOs\SyncOutcome;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Tasks\Services\UpdateTaskService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pulls events from the user's primary Google Calendar. Uses an incremental
 * sync_token when available; falls back to a wide initial window on first
 * sync or after a 410 Gone (sync_token expired).
 *
 * Recurring events are skipped in both directions for v1.
 */
final class PullEventsFromGoogleService
{
    private const INITIAL_BACK_DAYS = 30;

    private const INITIAL_FORWARD_DAYS = 365;

    public function __construct(
        private GoogleCalendarApiClient $api,
        private FindLinkByGoogleEventIdAction $findLinkByGoogleEventId,
        private UpsertGoogleCalendarLinkAction $upsertLink,
        private DeleteGoogleCalendarLinkAction $deleteLink,
        private UpdateGoogleCalendarConnectionAction $updateConnection,
        private CreateEventService $createEventService,
        private UpdateEventService $updateEventService,
        private DeleteEventService $deleteEventService,
        private FindEventService $findEventService,
        private UpdateTaskService $updateTaskService,
        private DeleteTaskService $deleteTaskService,
        private FindTaskService $findTaskService,
    ) {}

    public function execute(GoogleCalendarConnection $connection, SyncOutcome $outcome): void
    {
        $useSyncToken = $connection->sync_token !== null;
        $pageToken = null;
        $latestSyncToken = null;

        do {
            $params = $this->buildParams($connection, $useSyncToken, $pageToken);
            $response = $this->api->listEvents($connection, $params);

            if ($response->status() === 410) {
                // sync_token expired — reset and restart with a full window.
                DB::transaction(fn () => $this->updateConnection->execute($connection, ['sync_token' => null]));
                $connection->sync_token = null;

                $useSyncToken = false;
                $pageToken = null;
                $latestSyncToken = null;

                continue;
            }

            $response->throw();

            $body = $response->json();
            $items = is_array($body['items'] ?? null) ? $body['items'] : [];

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $this->processItem($connection, $item, $outcome);
            }

            $pageToken = is_string($body['nextPageToken'] ?? null) ? $body['nextPageToken'] : null;
            if (is_string($body['nextSyncToken'] ?? null)) {
                $latestSyncToken = $body['nextSyncToken'];
            }
        } while ($pageToken !== null);

        if ($latestSyncToken !== null) {
            DB::transaction(fn () => $this->updateConnection->execute($connection, ['sync_token' => $latestSyncToken]));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildParams(GoogleCalendarConnection $connection, bool $useSyncToken, ?string $pageToken): array
    {
        $params = [
            'singleEvents' => 'false',
            'maxResults' => 250,
            'showDeleted' => 'true',
        ];

        if ($pageToken !== null) {
            $params['pageToken'] = $pageToken;

            return $params;
        }

        if ($useSyncToken && $connection->sync_token !== null) {
            $params['syncToken'] = $connection->sync_token;

            return $params;
        }

        $params['timeMin'] = CarbonImmutable::now()->subDays(self::INITIAL_BACK_DAYS)->toRfc3339String();
        $params['timeMax'] = CarbonImmutable::now()->addDays(self::INITIAL_FORWARD_DAYS)->toRfc3339String();

        return $params;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function processItem(GoogleCalendarConnection $connection, array $item, SyncOutcome $outcome): void
    {
        $googleEventId = is_string($item['id'] ?? null) ? $item['id'] : null;
        if ($googleEventId === null) {
            $outcome->skipped++;

            return;
        }

        // v1: skip recurring events entirely.
        if (! empty($item['recurrence'])) {
            $outcome->skipped++;

            return;
        }

        $status = is_string($item['status'] ?? null) ? $item['status'] : 'confirmed';
        $link = $this->findLinkByGoogleEventId->execute($connection, $googleEventId);

        if ($status === 'cancelled') {
            if ($link === null) {
                $outcome->skipped++;

                return;
            }

            $this->deleteLinkedRow($connection, $link);
            $outcome->deletedLocal++;

            return;
        }

        $attributes = $this->mapEventAttributes($item);
        if ($attributes === null) {
            $outcome->skipped++;

            return;
        }

        $etag = is_string($item['etag'] ?? null) ? $item['etag'] : null;
        $googleUpdated = $this->parseTimestamp($item['updated'] ?? null);

        if ($link !== null) {
            // Update the local row and refresh its link in ONE transaction so a
            // failed link upsert rolls back the row change too (no orphan rows).
            DB::transaction(function () use ($connection, $link, $attributes, $googleUpdated, $googleEventId, $etag) {
                $this->updateLinkedRow($connection, $link->syncable, $attributes, $googleUpdated);
                $syncable = $link->syncable instanceof Model ? $link->syncable->fresh() ?? $link->syncable : null;
                if ($syncable !== null) {
                    $this->upsertLink->execute(
                        $connection,
                        $syncable,
                        $googleEventId,
                        $etag,
                        $syncable->updated_at ?? Carbon::now(),
                    );
                }
            });
            $outcome->pulled++;

            return;
        }

        // No link — look for dashyType/dashyLocalId hint.
        [$dashyType, $dashyLocalId] = $this->extractDashyHints($item);

        // Reconcile-or-create the local row and write its link in ONE
        // transaction: if the link upsert fails, the created/updated syncable
        // is rolled back rather than left orphaned without a sync link.
        DB::transaction(function () use ($connection, $dashyType, $dashyLocalId, $attributes, $googleEventId, $etag) {
            $syncable = $this->reconcileExisting($connection, $dashyType, $dashyLocalId, $attributes);

            if ($syncable === null) {
                $syncable = $this->createEventService->execute($connection->user, [
                    'title' => $attributes['title'],
                    'description' => $attributes['description'],
                    'start_at' => $attributes['start_at'],
                    'end_at' => $attributes['end_at'],
                    'is_all_day' => $attributes['is_all_day'],
                    'color' => EventColor::Danube->value,
                    'location' => $attributes['location'] ?? null,
                    'recurrence_freq' => RecurrenceFreq::None->value,
                    'recurrence_until' => null,
                ]);
            }

            $this->upsertLink->execute(
                $connection,
                $syncable,
                $googleEventId,
                $etag,
                $syncable->updated_at ?? Carbon::now(),
            );
        });

        $outcome->pulled++;
    }

    private function deleteLinkedRow(GoogleCalendarConnection $connection, $link): void
    {
        $syncable = $link->syncable;

        DB::transaction(function () use ($link, $syncable, $connection) {
            if ($syncable instanceof Event) {
                $this->deleteEventService->execute($connection->user, $syncable->id);
            } elseif ($syncable instanceof Task) {
                $this->deleteTaskService->execute($connection->user, $syncable->id);
            }
            $this->deleteLink->execute($link);
        });
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function mapEventAttributes(array $item): ?array
    {
        $start = $this->parseBoundary($item['start'] ?? null);
        $end = $this->parseBoundary($item['end'] ?? null);

        if ($start === null || $end === null) {
            return null;
        }

        $isAllDay = $start['isDate'] || $end['isDate'];
        // Google's end.date is exclusive; pull it back by one day for our schema.
        $endAt = ($isAllDay && $end['isDate'])
            ? $end['value']->copy()->subDay()
            : $end['value'];

        return [
            'title' => (string) ($item['summary'] ?? __('(no title)')),
            'description' => is_string($item['description'] ?? null) ? $item['description'] : null,
            'location' => is_string($item['location'] ?? null) ? $item['location'] : null,
            'start_at' => $start['value'],
            'end_at' => $endAt,
            'is_all_day' => $isAllDay,
        ];
    }

    /**
     * @param  mixed  $boundary
     * @return array{value: Carbon, isDate: bool}|null
     */
    private function parseBoundary($boundary): ?array
    {
        if (! is_array($boundary)) {
            return null;
        }

        if (is_string($boundary['dateTime'] ?? null)) {
            return [
                'value' => Carbon::parse($boundary['dateTime'])->setTimezone(config('app.timezone')),
                'isDate' => false,
            ];
        }

        if (is_string($boundary['date'] ?? null)) {
            return [
                'value' => Carbon::parse($boundary['date'], config('app.timezone'))->startOfDay(),
                'isDate' => true,
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function updateLinkedRow(GoogleCalendarConnection $connection, ?Model $syncable, array $attributes, ?Carbon $googleUpdated): void
    {
        if ($syncable === null) {
            return;
        }

        // Last-write-wins by timestamp.
        $localUpdated = $syncable->updated_at;
        if ($googleUpdated !== null && $localUpdated !== null && $googleUpdated->lessThanOrEqualTo($localUpdated)) {
            return;
        }

        if ($syncable instanceof Event) {
            $this->updateEventService->execute($connection->user, $syncable->id, [
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'location' => $attributes['location'],
                'start_at' => $attributes['start_at'],
                'end_at' => $attributes['end_at'],
                'is_all_day' => $attributes['is_all_day'],
            ]);

            return;
        }

        if ($syncable instanceof Task) {
            $this->updateTaskService->execute($connection->user, $syncable->id, [
                'name' => $attributes['title'],
                'description' => $attributes['description'],
                'start_date' => $attributes['start_at'],
                'end_date' => $attributes['end_at'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{0: string|null, 1: int|null}
     */
    private function extractDashyHints(array $item): array
    {
        $extended = $item['extendedProperties']['private'] ?? null;
        if (! is_array($extended)) {
            return [null, null];
        }

        $type = is_string($extended['dashyType'] ?? null) ? $extended['dashyType'] : null;
        $id = isset($extended['dashyLocalId']) && is_numeric($extended['dashyLocalId'])
            ? (int) $extended['dashyLocalId']
            : null;

        return [$type, $id];
    }

    /**
     * If Google sent a dashy-origin hint that points to a still-existing
     * local row, reuse it instead of creating a fresh Event. This is what
     * lets a push-then-pull round-trip avoid duplicates.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function reconcileExisting(GoogleCalendarConnection $connection, ?string $dashyType, ?int $dashyLocalId, array $attributes): ?Model
    {
        if ($dashyType === null || $dashyLocalId === null) {
            return null;
        }

        if ($dashyType === 'event') {
            try {
                $event = $this->findEventService->execute($connection->user, $dashyLocalId);
            } catch (ModelNotFoundException|AuthorizationException) {
                return null;
            }
            $this->updateLinkedRow($connection, $event, $attributes, null);

            return $event->fresh();
        }

        if ($dashyType === 'task') {
            try {
                $task = $this->findTaskService->execute($connection->user, $dashyLocalId);
            } catch (ModelNotFoundException|AuthorizationException) {
                return null;
            }
            // Only update timing/title — don't trigger assignment changes from Google.
            $this->updateLinkedRow($connection, $task, $attributes, null);

            return $task->fresh();
        }

        Log::warning('Unknown dashyType returned from Google Calendar.', ['type' => $dashyType]);

        return null;
    }

    /**
     * @param  mixed  $value
     */
    private function parseTimestamp($value): ?Carbon
    {
        if (! is_string($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
