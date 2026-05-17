<?php

namespace App\Domains\Calendar\Enums;

enum EventColor: string
{
    case Danube = 'danube';
    case Torea = 'torea';
    case Shilo = 'shilo';
    case Success = 'success';
    case Warning = 'warning';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Danube => __('Blue'),
            self::Torea => __('Deep blue'),
            self::Shilo => __('Pink'),
            self::Success => __('Green'),
            self::Warning => __('Amber'),
            self::Error => __('Red'),
        };
    }

    public function colorVar(): string
    {
        return match ($this) {
            self::Danube => '--color-brand-danube',
            self::Torea => '--color-brand-torea',
            self::Shilo => '--color-brand-shilo',
            self::Success => '--state-success',
            self::Warning => '--state-warning',
            self::Error => '--state-error',
        };
    }
}
