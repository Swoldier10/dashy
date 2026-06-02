<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Services\ListNotificationsForUserService;
use App\Domains\Notifications\Support\NotificationPresenter;
use App\Models\User;

/**
 * AUTO-READ. Lists the user's latest in-app notifications — the same feed
 * the bell panel shows. Capped at 50 results.
 */
final class ListNotificationsTool implements AiTool
{
    public function __construct(
        private ListNotificationsForUserService $listNotifications,
    ) {}

    public function name(): string
    {
        return 'list_notifications';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List the current user\'s latest notifications (task assignments, status changes, due '
            .'reminders, team membership changes, event reminders). Use this when the user asks what '
            .'they missed, what\'s new, or about recent activity addressed to them.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
                'unread_only' => ['type' => 'boolean'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $normalized = [];

        if (isset($arguments['limit']) && is_int($arguments['limit'])) {
            $normalized['limit'] = max(1, min(50, $arguments['limit']));
        }

        if (isset($arguments['unread_only']) && is_bool($arguments['unread_only'])) {
            $normalized['unread_only'] = $arguments['unread_only'];
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        $notifications = $this->listNotifications->execute(
            $user,
            limit: (int) ($arguments['limit'] ?? 30),
            unreadOnly: (bool) ($arguments['unread_only'] ?? false),
        );

        $presenter = new NotificationPresenter;

        return [
            'count' => $notifications->count(),
            'notifications' => $notifications->map(fn (Notification $n) => [
                'id' => $n->id,
                'type' => $n->type->value,
                'type_label' => $n->type->label(),
                'title' => $presenter->title($n->type, $n->data ?? []),
                'body' => $presenter->body($n->type, $n->data ?? []),
                'is_read' => $n->isRead(),
                'created_at' => $n->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
