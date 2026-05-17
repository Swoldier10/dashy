<?php

namespace App\Domains\Projects\Enums;

enum ProjectStatusCategory: string
{
    case NotStarted = 'not_started';
    case Active = 'active';
    case Done = 'done';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => __('Not started'),
            self::Active => __('Active'),
            self::Done => __('Done'),
            self::Closed => __('Closed'),
        };
    }

    public function colorVar(): string
    {
        return match ($this) {
            self::NotStarted => '--ink-dim',
            self::Active => '--state-warning',
            self::Done, self::Closed => '--state-success',
        };
    }
}
