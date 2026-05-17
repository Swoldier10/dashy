<?php

namespace App\Domains\Calendar\Enums;

enum RecurrenceFreq: string
{
    case None = 'none';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::None => __('Does not repeat'),
            self::Daily => __('Daily'),
            self::Weekly => __('Weekly'),
            self::Monthly => __('Monthly'),
            self::Yearly => __('Yearly'),
        };
    }
}
