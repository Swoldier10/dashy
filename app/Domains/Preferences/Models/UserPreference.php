<?php

namespace App\Domains\Preferences\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Key/JSON store, one row per (user_id, key). Free-form memories the user
 * (or the assistant on their behalf) wants to persist across chat sessions
 * use a key prefixed with "memory." — structured prefs (default language,
 * default project, etc.) use bare keys.
 *
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property mixed $value
 */
class UserPreference extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
