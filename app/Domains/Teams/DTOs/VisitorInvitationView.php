<?php

namespace App\Domains\Teams\DTOs;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Represents what the public invite page should show for the current visitor.
 *
 * `status` drives which UI branch renders. All other fields are nullable —
 * only set on the branches that need them.
 */
final readonly class VisitorInvitationView
{
    public const STATUS_INVALID = 'invalid';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_ACCEPTED_BY_OTHER = 'accepted_by_other';

    public const STATUS_EMAIL_MISMATCH = 'email_mismatch';

    public const STATUS_ALREADY_MEMBER = 'already_member';

    public const STATUS_READY_TO_ACCEPT = 'ready_to_accept';

    public const STATUS_NEEDS_LOGIN = 'needs_login';

    public const STATUS_NEEDS_REGISTER = 'needs_register';

    public function __construct(
        public string $status,
        public ?Team $team = null,
        public ?User $inviter = null,
        public ?TeamRole $role = null,
        public ?string $boundEmail = null,
        public ?CarbonImmutable $expiresAt = null,
        public ?TeamInvitation $invitation = null,
    ) {}

    public static function invalid(): self
    {
        return new self(status: self::STATUS_INVALID);
    }
}
