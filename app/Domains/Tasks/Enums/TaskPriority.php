<?php

namespace App\Domains\Tasks\Enums;

enum TaskPriority: string
{
    case Urgent = 'urgent';
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::Urgent => __('Urgent'),
            self::High => __('High'),
            self::Normal => __('Normal'),
            self::Low => __('Low'),
        };
    }

    /** Compact row-pill label used in task rows (matches the design mockup). */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Urgent => __('Urg'),
            self::High => __('High'),
            self::Normal => __('Med'),
            self::Low => __('Low'),
        };
    }

    public function colorVar(): string
    {
        return match ($this) {
            self::Urgent => '--state-error',
            self::High => '--state-warning',
            self::Normal => '--blue',
            self::Low => '--ink-dim',
        };
    }
}
