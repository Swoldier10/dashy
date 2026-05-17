<?php

namespace App\Domains\Teams\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => __('Owner'),
            self::Member => __('Member'),
        };
    }
}
