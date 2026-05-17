<?php

namespace App\Domains\Calendar\Enums;

enum AgendaKind: string
{
    case Event = 'event';
    case Task = 'task';
}
