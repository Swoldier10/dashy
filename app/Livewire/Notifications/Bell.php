<?php

namespace App\Livewire\Notifications;

use App\Domains\Notifications\Services\CountUnreadNotificationsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Notification bell with unread badge. Rendered twice in the app sidebar
 * component (desktop sidebar + mobile topbar) via the $variant prop; the
 * wire:poll uses .visible so only the variant shown at the current
 * breakpoint actually polls.
 */
class Bell extends Component
{
    public string $variant = 'sidebar';

    #[Computed]
    public function unreadCount(): int
    {
        return app(CountUnreadNotificationsService::class)->execute(Auth::user());
    }

    public function open(): void
    {
        $this->dispatch('notifications-panel:open');
    }

    /**
     * Re-render (and so recount) immediately when the panel marks
     * notifications read, instead of waiting for the next poll.
     */
    #[On('notifications:read')]
    public function refreshBadge(): void
    {
        unset($this->unreadCount);
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}
