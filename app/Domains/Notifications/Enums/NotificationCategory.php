<?php

namespace App\Domains\Notifications\Enums;

enum NotificationCategory: string
{
    case Tasks = 'tasks';
    case Teams = 'teams';
    case Calendar = 'calendar';

    public function label(): string
    {
        return match ($this) {
            self::Tasks => __('Tasks'),
            self::Teams => __('Teams'),
            self::Calendar => __('Calendar'),
        };
    }
}
