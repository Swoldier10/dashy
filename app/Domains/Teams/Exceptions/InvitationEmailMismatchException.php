<?php

namespace App\Domains\Teams\Exceptions;

class InvitationEmailMismatchException extends TeamInvitationException
{
    public function __construct(public readonly string $boundEmail)
    {
        parent::__construct();
    }
}
