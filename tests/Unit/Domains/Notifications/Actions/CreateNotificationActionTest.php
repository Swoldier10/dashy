<?php

namespace Tests\Unit\Domains\Notifications\Actions;

use App\Domains\Notifications\Actions\CreateNotificationAction;
use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateNotificationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_notification_row_from_the_payload(): void
    {
        $recipient = User::factory()->create();
        $actor = User::factory()->create();

        $notification = (new CreateNotificationAction)->execute(new NotificationPayload(
            type: NotificationType::TaskAssigned,
            recipientUserId: $recipient->id,
            actorUserId: $actor->id,
            subjectType: 'task',
            subjectId: 42,
            data: ['task_id' => 42, 'task_name' => 'Fix it', 'actor_name' => 'Anna'],
        ));

        $this->assertNotNull($notification);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => NotificationType::TaskAssigned->value,
            'subject_type' => 'task',
            'subject_id' => 42,
            'read_at' => null,
        ]);
        $this->assertSame('Fix it', $notification->data['task_name']);
    }

    public function test_returns_null_when_the_dedupe_key_already_exists(): void
    {
        $recipient = User::factory()->create();
        $action = new CreateNotificationAction;

        $payload = new NotificationPayload(
            type: NotificationType::TaskDueSoon,
            recipientUserId: $recipient->id,
            data: ['task_id' => 1],
            dedupeKey: 'task_due_soon:1:'.$recipient->id.':1780000000',
        );

        $this->assertNotNull($action->execute($payload));
        $this->assertNull($action->execute($payload));
        $this->assertSame(1, Notification::query()->count());
    }

    public function test_null_dedupe_keys_never_collide(): void
    {
        $recipient = User::factory()->create();
        $action = new CreateNotificationAction;

        $payload = new NotificationPayload(
            type: NotificationType::TaskAssigned,
            recipientUserId: $recipient->id,
            data: [],
        );

        $this->assertNotNull($action->execute($payload));
        $this->assertNotNull($action->execute($payload));
        $this->assertSame(2, Notification::query()->count());
    }

    public function test_rethrows_constraint_violations_unrelated_to_dedupe(): void
    {
        $this->expectException(QueryException::class);

        (new CreateNotificationAction)->execute(new NotificationPayload(
            type: NotificationType::TaskAssigned,
            recipientUserId: 999999,
            data: [],
        ));
    }
}
