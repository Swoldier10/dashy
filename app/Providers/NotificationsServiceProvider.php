<?php

namespace App\Providers;

use App\Domains\Notifications\Listeners\HandleTeamMemberRemoved;
use App\Domains\Notifications\Listeners\SendTaskAssignmentNotifications;
use App\Domains\Notifications\Listeners\SendTaskCreatedNotifications;
use App\Domains\Notifications\Listeners\SendTaskFieldChangeNotifications;
use App\Domains\Notifications\Listeners\SendTaskStatusNotifications;
use App\Domains\Notifications\Listeners\SendTeamMembershipNotifications;
use App\Domains\Tasks\Events\TaskAssigned;
use App\Domains\Tasks\Events\TaskAttachmentAdded;
use App\Domains\Tasks\Events\TaskCreatedInProject;
use App\Domains\Tasks\Events\TaskDueDateChanged;
use App\Domains\Tasks\Events\TaskPriorityChanged;
use App\Domains\Tasks\Events\TaskStatusChanged;
use App\Domains\Tasks\Events\TaskUnassigned;
use App\Domains\Teams\Events\TeamInvitationAccepted;
use App\Domains\Teams\Events\TeamMemberJoined;
use App\Domains\Teams\Events\TeamMemberRemoved;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the Notifications domain to the owning domains' events. The owning
 * domains emit snapshot-bearing events after commit and know nothing about
 * Notifications — the dependency points consumer → producer, mirroring the
 * Search domain's wiring.
 */
class NotificationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(TaskAssigned::class, SendTaskAssignmentNotifications::class);
        Event::listen(TaskUnassigned::class, SendTaskAssignmentNotifications::class);
        Event::listen(TaskStatusChanged::class, SendTaskStatusNotifications::class);
        Event::listen(TaskDueDateChanged::class, SendTaskFieldChangeNotifications::class);
        Event::listen(TaskPriorityChanged::class, SendTaskFieldChangeNotifications::class);
        Event::listen(TaskAttachmentAdded::class, SendTaskFieldChangeNotifications::class);
        Event::listen(TaskCreatedInProject::class, SendTaskCreatedNotifications::class);
        Event::listen(TeamInvitationAccepted::class, SendTeamMembershipNotifications::class);
        Event::listen(TeamMemberJoined::class, SendTeamMembershipNotifications::class);
        Event::listen(TeamMemberRemoved::class, HandleTeamMemberRemoved::class);
    }
}
