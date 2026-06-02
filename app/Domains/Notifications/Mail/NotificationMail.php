<?php

namespace App\Domains\Notifications\Mail;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Support\NotificationPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Generic notification e-mail. Carries scalars only (never the notification
 * model) so queued jobs are immune to row deletion; all copy and URLs come
 * from NotificationPresenter.
 */
class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly NotificationType $type,
        public readonly array $data,
        public readonly string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: (new NotificationPresenter)->title($this->type, $this->data),
        );
    }

    public function content(): Content
    {
        $presenter = new NotificationPresenter;

        return new Content(
            view: 'mail.notification',
            text: 'mail.notification-text',
            with: [
                'headline' => $presenter->title($this->type, $this->data),
                'body' => $presenter->body($this->type, $this->data),
                'ctaUrl' => $presenter->ctaUrl($this->type, $this->data),
                'ctaLabel' => $presenter->ctaLabel($this->type),
                'recipientName' => $this->recipientName,
            ],
        );
    }
}
