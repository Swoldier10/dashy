<?php

namespace App\Domains\Auth\Enums;

enum Salutation: string
{
    case Mr = 'mr';
    case Ms = 'ms';
    case Mx = 'mx';
    case Dr = 'dr';

    public function label(): string
    {
        return match ($this) {
            self::Mr => 'Mr',
            self::Ms => 'Ms',
            self::Mx => 'Mx',
            self::Dr => 'Dr',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
