<?php

namespace App\Domains\Teams\Enums;

enum Currency: string
{
    case CHF = 'CHF';
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';

    public function label(): string
    {
        return match ($this) {
            self::CHF => __('Swiss Franc (CHF)'),
            self::EUR => __('Euro (EUR)'),
            self::USD => __('US Dollar (USD)'),
            self::GBP => __('British Pound (GBP)'),
        };
    }
}
