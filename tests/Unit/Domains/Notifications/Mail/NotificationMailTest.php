<?php

namespace Tests\Unit\Domains\Notifications\Mail;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Mail\NotificationMail;
use Tests\TestCase;

class NotificationMailTest extends TestCase
{
    private function mailable(): NotificationMail
    {
        return new NotificationMail(
            NotificationType::TaskAssigned,
            ['task_id' => 42, 'task_name' => 'Fix the build', 'actor_name' => 'Anna', 'project_name' => 'Website'],
            'Raul',
        );
    }

    public function test_subject_is_the_presenter_title(): void
    {
        $this->assertSame(
            'Anna assigned you to "Fix the build"',
            $this->mailable()->envelope()->subject,
        );
    }

    public function test_html_contains_headline_greeting_and_cta(): void
    {
        $mailable = $this->mailable();

        $mailable->assertSeeInHtml('Anna assigned you to "Fix the build"');
        $mailable->assertSeeInHtml('Hi Raul,');
        $mailable->assertSeeInHtml(route('tasks').'?task=42');
        $mailable->assertSeeInHtml('View task');
    }

    public function test_text_variant_contains_headline_and_link(): void
    {
        $mailable = $this->mailable();

        $mailable->assertSeeInText('Anna assigned you to "Fix the build"');
        $mailable->assertSeeInText(route('tasks').'?task=42');
    }

    public function test_cta_is_omitted_when_the_subject_id_is_missing(): void
    {
        $mailable = new NotificationMail(NotificationType::TaskAssigned, ['task_name' => 'Fix'], 'Raul');

        $mailable->assertDontSeeInHtml('View task');
    }
}
