<?php

namespace App\Domains\Teams\Models;

use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Enums\Currency;
use App\Domains\Teams\Enums\TeamRole;
use App\Models\User;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    protected $fillable = ['name', 'personal_team', 'logo', 'hourly_rate', 'currency'];

    protected $casts = [
        'personal_team' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'currency' => Currency::class,
    ];

    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->using(TeamMember::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->members()->wherePivot('role', TeamRole::Owner->value);
    }

    public function hasMember(User $user): bool
    {
        return $this->members
            ->contains(fn (User $member) => $member->is($user));
    }

    public function roleFor(User $user): ?TeamRole
    {
        $member = $this->members->first(fn (User $m) => $m->is($user));
        if ($member === null) {
            return null;
        }

        $role = $member->pivot->role ?? null;
        if ($role instanceof TeamRole) {
            return $role;
        }

        return is_string($role) ? TeamRole::from($role) : null;
    }

    public function initials(): string
    {
        return Str::of((string) $this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
