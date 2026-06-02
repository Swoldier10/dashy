<?php

namespace App\Livewire\Notifications;

use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Services\ListNotificationsForUserService;
use App\Domains\Notifications\Services\MarkAllNotificationsReadService;
use App\Domains\Notifications\Services\MarkNotificationReadService;
use App\Domains\Notifications\Support\NotificationPresenter;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Global notification feed drawer — one instance in the app shell. The list
 * loads lazily: nothing is queried until a bell dispatches
 * notifications-panel:open.
 */
class Panel extends Component
{
    use DispatchesDashyUi;

    public bool $open = false;

    #[On('notifications-panel:open')]
    public function openPanel(): void
    {
        $this->open = true;
        unset($this->notifications);
        $this->openModal('notifications-panel');
    }

    public function closePanel(): void
    {
        $this->open = false;
    }

    #[Computed]
    public function notifications()
    {
        if (! $this->open) {
            return collect();
        }

        return app(ListNotificationsForUserService::class)->execute(Auth::user());
    }

    /**
     * Presenter-rendered view models — the same copy the e-mails use.
     *
     * @return list<array{id: int, icon: string, title: string, body: ?string, time: string, read: bool}>
     */
    #[Computed]
    public function rows(): array
    {
        $presenter = new NotificationPresenter;

        return $this->notifications
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'icon' => $notification->type->icon(),
                'title' => $presenter->title($notification->type, $notification->data ?? []),
                'body' => $presenter->body($notification->type, $notification->data ?? []),
                'time' => $notification->created_at->diffForHumans(),
                'read' => $notification->isRead(),
            ])
            ->values()
            ->all();
    }

    public function openNotification(int $notificationId): void
    {
        /** @var ?Notification $notification */
        $notification = $this->notifications->firstWhere('id', $notificationId);

        if ($notification === null) {
            return;
        }

        app(MarkNotificationReadService::class)->execute(Auth::user(), $notificationId);
        $this->dispatch('notifications:read');
        $this->closeModal('notifications-panel');

        $url = (new NotificationPresenter)->ctaUrl($notification->type, $notification->data ?? []);

        if ($url !== null) {
            $this->redirect($url, navigate: true);
        }
    }

    public function markAllRead(): void
    {
        app(MarkAllNotificationsReadService::class)->execute(Auth::user());

        unset($this->notifications, $this->rows);
        $this->dispatch('notifications:read');
    }

    public function render()
    {
        return view('livewire.notifications.panel');
    }
}
