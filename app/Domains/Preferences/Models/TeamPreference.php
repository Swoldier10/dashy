<?php

namespace App\Domains\Preferences\Models;

use App\Domains\Teams\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Key/JSON store, one row per (team_id, key). Team-wide conventions live
 * here — "we use Fibonacci point estimates", "all task descriptions in
 * English", etc. — surfaced into the system prompt when a team is in focus.
 *
 * @property int $id
 * @property int $team_id
 * @property string $key
 * @property mixed $value
 */
class TeamPreference extends Model
{
    protected $fillable = ['team_id', 'key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
