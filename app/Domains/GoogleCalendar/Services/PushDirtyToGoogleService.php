<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Actions\DeleteGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\Actions\FindLinkForSyncableAction;
use App\Domains\GoogleCalendar\Actions\ListDirtyEventsForUserAction;
use App\Domains\GoogleCalendar\Actions\ListDirtyTasksForUserAction;
use App\Domains\GoogleCalendar\Actions\UpsertGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\DTOs\SyncOutcome;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use App\Domains\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pushes pending local changes to Google: inserts/updates for dirty rows,
 * and deletes for links whose local row has disappeared (an orphan check).
 * On HTTP 412 (etag mismatch) we surrender the round so the next pull pass
 * brings Google's version down — Google wins on conflict.
 */
final class PushDirtyToGoogleService
{
    public function __construct(
        private GoogleCalendarApiClient $api,
        private MapLocalToGooglePayloadService $mapper,
        private ListDirtyEventsForUserAction $listDirtyEvents,
        private ListDirtyTasksForUserAction $listDirtyTasks,
        private FindLinkForSyncableAction $findLink,
        private UpsertGoogleCalendarLinkAction $upsertLink,
        private DeleteGoogleCalendarLinkAction $deleteLink,
    ) {}

    public function execute(GoogleCalendarConnection $connection, SyncOutcome $outcome): void
    {
        $this->pushCollection($connection, $this->listDirtyEvents->execute($connection), $outcome);
        $this->pushCollection($connection, $this->listDirtyTasks->execute($connection), $outcome);
        $this->pushOrphanDeletes($connection, $outcome);
    }

    /**
     * @param  Collection<int, Model>  $rows
     */
    private function pushCollection(GoogleCalendarConnection $connection, Collection $rows, SyncOutcome $outcome): void
    {
        foreach ($rows as $row) {
            $link = $this->findLink->execute($connection, $row);
            $payload = $this->mapper->execute($row);

            $response = $link === null
                ? $this->api->insertEvent($connection, $payload)
                : $this->api->patchEvent($connection, $link->google_event_id, $payload, $link->etag);

            if ($response->status() === 412) {
                // Google wins on etag mismatch; next pull will reconcile.
                $outcome->skipped++;

                continue;
            }

            if (! $response->successful()) {
                Log::warning('Google Calendar push failed.', [
                    'syncable_type' => $row::class,
                    'syncable_id' => $row->getKey(),
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                $outcome->errors++;

                continue;
            }

            $body = $response->json();
            $googleEventId = is_string($body['id'] ?? null) ? $body['id'] : ($link?->google_event_id);
            $etag = is_string($body['etag'] ?? null) ? $body['etag'] : null;

            if ($googleEventId === null) {
                $outcome->errors++;

                continue;
            }

            DB::transaction(fn () => $this->upsertLink->execute(
                $connection,
                $row,
                $googleEventId,
                $etag,
                $row->updated_at ?? Carbon::now(),
            ));

            $outcome->pushed++;
        }
    }

    private function pushOrphanDeletes(GoogleCalendarConnection $connection, SyncOutcome $outcome): void
    {
        $orphans = $this->listOrphanedLinks($connection);

        foreach ($orphans as $link) {
            $response = $this->api->deleteEvent($connection, $link->google_event_id);

            if ($response->status() === 404 || $response->status() === 410 || $response->successful()) {
                DB::transaction(fn () => $this->deleteLink->execute($link));
                $outcome->deletedRemote++;

                continue;
            }

            Log::warning('Google Calendar remote delete failed.', [
                'link_id' => $link->id,
                'google_event_id' => $link->google_event_id,
                'status' => $response->status(),
            ]);
            $outcome->errors++;
        }
    }

    /**
     * @return Collection<int, GoogleCalendarLink>
     */
    private function listOrphanedLinks(GoogleCalendarConnection $connection): Collection
    {
        $eventOrphans = GoogleCalendarLink::query()
            ->where('connection_id', $connection->id)
            ->where('syncable_type', Event::class)
            ->whereNotIn('syncable_id', function ($q) {
                $q->select('id')->from('calendar_events');
            })
            ->get();

        $taskOrphans = GoogleCalendarLink::query()
            ->where('connection_id', $connection->id)
            ->where('syncable_type', Task::class)
            ->whereNotIn('syncable_id', function ($q) {
                $q->select('id')->from('tasks');
            })
            ->get();

        return $eventOrphans->concat($taskOrphans);
    }
}
