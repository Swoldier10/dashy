<?php

namespace App\Domains\Teams\Mail;

use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly TeamInvitation $invitation,
        public readonly string $plainToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("You're invited to join :team on Dashy", [
                'team' => $this->invitation->team?->name ?? 'a team',
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.team-invitation',
            text: 'mail.team-invitation-text',
            with: [
                'team' => $this->invitation->team,
                'inviter' => $this->invitation->invitedBy,
                'role' => $this->invitation->role?->label() ?? '',
                'acceptUrl' => route('invite.show', ['token' => $this->plainToken]),
                'expiresAt' => $this->invitation->expires_at,
            ],
        );
    }
}
