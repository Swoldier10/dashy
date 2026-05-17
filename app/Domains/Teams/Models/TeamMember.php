<?php

namespace App\Domains\Teams\Models;

use App\Domains\Teams\Enums\TeamRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamMember extends Pivot
{
    protected $table = 'team_user';

    public $incrementing = true;

    protected $casts = [
        'role' => TeamRole::class,
    ];
}
