<?php

namespace App\Domains\Teams\Exceptions;

class InvitationResendThrottledException extends TeamInvitationException
{
    public function __construct(public readonly int $retryAfterSeconds)
    {
        parent::__construct();
    }
}
