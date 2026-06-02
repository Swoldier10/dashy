<?php

namespace Tests\Unit\Domains\Notifications\Services;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Mail\NotificationMail;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Services\NotifyUserService;
use App\Domains\Notifications\Services\UpdateNotificationPreferencesService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyUserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function payload(User $recipient, array $overrides = []): NotificationPayload
    {
        return new NotificationPayload(...array_merge([
            'type' => NotificationType::TaskAssigned,
            'recipientUserId' => $recipient->id,
            'actorUserId' => null,
            'data' => ['task_id' => 1, 'task_name' => 'Fix', 'actor_name' => 'Anna'],
        ], $overrides));
    }

    public function test_never_notifies_the_actor_about_their_own_action(): void
    {
        $user = User::factory()->create();

        app(NotifyUserService::class)->execute($this->payload($user, ['actorUserId' => $user->id]));

        $this->assertSame(0, Notification::query()->count());
        Mail::assertNothingOutgoing();
    }

    public function test_creates_app_row_and_queues_email_when_both_channels_are_on(): void
    {
        $recipient = User::factory()->create();

        app(NotifyUserService::class)->execute($this->payload($recipient));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipient->id,
            'type' => NotificationType::TaskAssigned->value,
            'read_at' => null,
        ]);
        Mail::assertQueued(
            NotificationMail::class,
            fn (NotificationMail $mail) => $mail->hasTo($recipient->email)
                && $mail->type === NotificationType::TaskAssigned,
        );
    }

    public function test_respects_a_disabled_email_channel(): void
    {
        $recipient = User::factory()->create();
        app(UpdateNotificationPreferencesService::class)->execute($recipient, [
            'task_assigned' => ['email' => false, 'app' => true],
        ]);

        app(NotifyUserService::class)->execute($this->payload($recipient));

        $this->assertSame(1, Notification::query()->count());
        Mail::assertNothingOutgoing();
    }

    public function test_event_notification_with_app_off_sends_email_without_a_row(): void
    {
        $recipient = User::factory()->create();
        app(UpdateNotificationPreferencesService::class)->execute($recipient, [
            'task_assigned' => ['email' => true, 'app' => false],
        ]);

        app(NotifyUserService::class)->execute($this->payload($recipient));

        $this->assertSame(0, Notification::query()->count());
        Mail::assertQueued(NotificationMail::class, 1);
    }

    public function test_does_nothing_when_both_channels_are_off(): void
    {
        $recipient = User::factory()->create();
        app(UpdateNotificationPreferencesService::class)->execute($recipient, [
            'task_assigned' => ['email' => false, 'app' => false],
        ]);

        app(NotifyUserService::class)->execute($this->payload($recipient));

        $this->assertSame(0, Notification::query()->count());
        Mail::assertNothingOutgoing();
    }

    public function test_a_dedupe_hit_skips_both_the_row_and_the_email(): void
    {
        $recipient = User::factory()->create();
        $payload = $this->payload($recipient, [
            'type' => NotificationType::TaskDueSoon,
            'dedupeKey' => 'task_due_soon:1:'.$recipient->id.':1780000000',
        ]);

        app(NotifyUserService::class)->execute($payload);
        app(NotifyUserService::class)->execute($payload);

        $this->assertSame(1, Notification::query()->count());
        Mail::assertQueued(NotificationMail::class, 1);
    }

    public function test_reminder_with_app_off_keeps_a_pre_read_ledger_row_and_emails_once(): void
    {
        $recipient = User::factory()->create();
        app(UpdateNotificationPreferencesService::class)->execute($recipient, [
            'task_due_soon' => ['email' => true, 'app' => false],
        ]);
        $payload = $this->payload($recipient, [
            'type' => NotificationType::TaskDueSoon,
            'dedupeKey' => 'task_due_soon:1:'.$recipient->id.':1780000000',
        ]);

        app(NotifyUserService::class)->execute($payload);
        app(NotifyUserService::class)->execute($payload);

        $ledger = Notification::query()->sole();
        $this->assertNotNull($ledger->read_at, 'Ledger row must be pre-read so it never lights the badge.');
        Mail::assertQueued(NotificationMail::class, 1);
    }

    public function test_returns_silently_when_the_recipient_no_longer_exists(): void
    {
        $ghost = User::factory()->create();
        $payload = $this->payload($ghost);
        $ghost->delete();

        app(NotifyUserService::class)->execute($payload);

        $this->assertSame(0, Notification::query()->count());
        Mail::assertNothingOutgoing();
    }
}
