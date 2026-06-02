<?php

namespace App\Domains\Notifications\Enums;

enum NotificationChannel: string
{
    case App = 'app';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::App => __('App'),
            self::Email => __('E-mail'),
        };
    }
}
