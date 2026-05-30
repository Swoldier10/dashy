<?php

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\CreateTeamService;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Teams')] class extends Component
{
    use DispatchesDashyUi;

    public string $name = '';

    public function createTeam(CreateTeamService $service): void
    {
        try {
            $team = $service->execute(Auth::user(), [
                'name' => $this->name,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }

        $this->reset('name');
        $this->toast('success', __('Team created.'));

        $this->redirect(route('teams.show', $team), navigate: true);
    }

    /**
     * @return Collection<int, Team>
     */
    #[Computed]
    public function teams(): Collection
    {
        return app(ListTeamsForUserService::class)->execute(Auth::user());
    }

    public function roleLabelFor(Team $team): string
    {
        $role = $team->pivot->role ?? null;
        if ($role instanceof TeamRole) {
            return $role->label();
        }

        return is_string($role) ? TeamRole::from($role)->label() : '';
    }
}; ?>

<div class="mx-auto flex w-full max-w-3xl flex-col gap-10 px-6 pt-10 pb-16 sm:px-6 lg:px-8">
    <div>
        <h1 class="font-display text-3xl" style="color: var(--ink);">{{ __('Teams') }}</h1>
        <p class="mt-2 text-sm" style="color: var(--ink-muted);">
            {{ __('Create teams, invite people, and manage your memberships.') }}
        </p>
        <div class="mt-6 border-t" style="border-color: var(--border);"></div>
    </div>

    {{-- Create team --}}
    <section class="space-y-4">
        <h2 class="font-display text-xl" style="color: var(--ink);">{{ __('Create a team') }}</h2>
        <form wire:submit="createTeam" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <x-dashy.input
                    wire:model="name"
                    :label="__('Team name')"
                    :placeholder="__('e.g. Acme Inc.')"
                    required
                    autocomplete="off"
                    data-test="create-team-name"
                />
            </div>
            <x-dashy.button
                variant="primary"
                type="submit"
                class="w-full sm:w-auto"
                data-test="create-team-button"
            >
                {{ __('Create team') }}
            </x-dashy.button>
        </form>
    </section>

    <div class="border-t" style="border-color: var(--border);"></div>

    {{-- Your teams --}}
    <section class="space-y-4">
        <h2 class="font-display text-xl" style="color: var(--ink);">{{ __('Your teams') }}</h2>

        <div class="flex flex-col gap-2">
            @forelse ($this->teams as $team)
                <a
                    wire:key="team-row-{{ $team->id }}"
                    href="{{ route('teams.show', $team) }}"
                    wire:navigate
                    class="flex flex-col gap-3 rounded-xl border p-4 transition sm:flex-row sm:items-center sm:gap-4"
                    style="border-color: var(--border-mid); background-color: var(--surface);"
                    onmouseover="this.style.backgroundColor='var(--surface-2)'"
                    onmouseout="this.style.backgroundColor='var(--surface)'"
                    data-test="team-row-{{ $team->id }}"
                >
                    <x-dashy.avatar
                        size="sm"
                        shape="square"
                        :name="$team->name"
                        :initials="$team->initials()"
                        :src="$team->logo"
                    />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate text-sm font-medium" style="color: var(--ink);">
                                {{ $team->name }}
                            </span>
                            @if ($team->personal_team)
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    style="background-color: var(--surface-2); color: var(--ink-muted);"
                                >
                                    {{ __('Personal') }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs" style="color: var(--ink-muted);">
                            {{ trans_choice('{1} 1 member|[2,*] :count members', $team->members_count, ['count' => $team->members_count]) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 sm:gap-4">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs"
                            style="background-color: var(--accent); color: var(--bg-deep);"
                        >
                            {{ $this->roleLabelFor($team) }}
                        </span>
                        <x-dashy.icon name="chevron-right" class="size-4" style="color: var(--ink-dim);" />
                    </div>
                </a>
            @empty
                <p class="text-sm" style="color: var(--ink-dim);">
                    {{ __('You\'re not a member of any teams yet.') }}
                </p>
            @endforelse
        </div>
    </section>
</div>
