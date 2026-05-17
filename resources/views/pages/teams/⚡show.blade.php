<?php

use App\Domains\Teams\Actions\FindTeamForUserAction;
use App\Domains\Teams\Enums\Currency;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\AddTeamMemberService;
use App\Domains\Teams\Services\DeleteTeamService;
use App\Domains\Teams\Services\RemoveTeamMemberService;
use App\Domains\Teams\Services\RenameTeamService;
use App\Domains\Teams\Services\TeamLogoService;
use App\Domains\Teams\Services\UpdateTeamRateService;
use App\Models\User;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('Team')] class extends Component
{
    use DispatchesDashyUi;

    use WithFileUploads;

    public int $teamId;

    public string $newName = '';

    public string $inviteEmail = '';

    public ?int $confirmRemoveMemberId = null;

    public ?TemporaryUploadedFile $newLogo = null;

    public string $hourlyRate = '';

    public string $currency = '';

    public function mount(int $team): void
    {
        $resolved = app(FindTeamForUserAction::class)->execute(Auth::user(), $team);
        if ($resolved === null) {
            abort(404);
        }

        $this->teamId = $resolved->id;
        $this->newName = $resolved->name;
        $this->hourlyRate = $resolved->hourly_rate !== null ? (string) $resolved->hourly_rate : '';
        $this->currency = $resolved->currency?->value ?? '';
    }

    #[Computed]
    public function team(): Team
    {
        $team = app(FindTeamForUserAction::class)->execute(Auth::user(), $this->teamId);
        if ($team === null) {
            abort(404);
        }

        return $team;
    }

    #[Computed]
    public function isOwner(): bool
    {
        $role = $this->team->roleFor(Auth::user());

        return $role === TeamRole::Owner;
    }

    #[Computed]
    public function isLastOwner(): bool
    {
        $owners = $this->team->members->filter(function (User $member) {
            $role = $member->pivot->role ?? null;
            $value = $role instanceof TeamRole ? $role->value : $role;

            return $value === TeamRole::Owner->value;
        });

        return $owners->count() === 1
            && $owners->first()->is(Auth::user());
    }

    public function rename(RenameTeamService $service): void
    {
        $service->execute(Auth::user(), $this->team, ['name' => $this->newName]);

        unset($this->team);

        $this->toast('success', __('Team renamed.'));
    }

    public function updateRate(UpdateTeamRateService $service): void
    {
        $team = $service->execute(Auth::user(), $this->team, [
            'hourly_rate' => $this->hourlyRate,
            'currency' => $this->currency,
        ]);

        $this->hourlyRate = $team->hourly_rate !== null ? (string) $team->hourly_rate : '';
        $this->currency = $team->currency?->value ?? '';

        unset($this->team);

        $this->toast('success', __('Hourly rate updated.'));
    }

    public function addMember(AddTeamMemberService $service): void
    {
        try {
            $service->execute(Auth::user(), $this->team, ['email' => $this->inviteEmail]);
        } catch (ValidationException $e) {
            throw $e;
        }

        $this->reset('inviteEmail');
        unset($this->team);

        $this->toast('success', __('Member added.'));
    }

    public function confirmRemoveMember(int $memberId): void
    {
        $this->confirmRemoveMemberId = $memberId;
        $this->openModal('confirm-remove-member');
    }

    public function cancelRemoveMember(): void
    {
        $this->confirmRemoveMemberId = null;
        $this->closeModal('confirm-remove-member');
    }

    public function removeMember(RemoveTeamMemberService $service): void
    {
        if ($this->confirmRemoveMemberId === null) {
            return;
        }

        $target = User::find($this->confirmRemoveMemberId);
        if ($target === null) {
            $this->confirmRemoveMemberId = null;
            $this->closeModal('confirm-remove-member');

            return;
        }

        $service->execute(Auth::user(), $this->team, $target);

        $isSelfLeave = $target->is(Auth::user());
        $this->confirmRemoveMemberId = null;
        $this->closeModal('confirm-remove-member');

        if ($isSelfLeave) {
            $this->toast('success', __('You left the team.'));
            $this->redirect(route('teams.index'), navigate: true);

            return;
        }

        unset($this->team);
        $this->toast('success', __('Member removed.'));
    }

    public function deleteTeam(DeleteTeamService $service): void
    {
        $service->execute(Auth::user(), $this->team);

        $this->toast('success', __('Team deleted.'));
        $this->redirect(route('teams.index'), navigate: true);
    }

    public function updatedNewLogo(TeamLogoService $service): void
    {
        if ($this->newLogo === null) {
            return;
        }

        try {
            $service->upload(Auth::user(), $this->team, $this->newLogo);
        } catch (ValidationException $e) {
            $this->newLogo = null;
            throw $e;
        }

        $this->newLogo = null;
        unset($this->team);

        $this->toast('success', __('Logo updated.'));
    }

    public function removeLogo(TeamLogoService $service): void
    {
        $service->remove(Auth::user(), $this->team);

        unset($this->team);

        $this->toast('success', __('Logo removed.'));
    }

    public function roleLabel(?TeamRole $role): string
    {
        return $role?->label() ?? '';
    }
}; ?>

@php
    $team = $this->team;
    $isOwner = $this->isOwner;
@endphp

<div class="mx-auto flex w-full max-w-3xl flex-col gap-6 px-6 pt-10 pb-16 sm:px-6 lg:px-8">
    {{-- Heading --}}
    <div>
        <a
            href="{{ route('teams.index') }}"
            wire:navigate
            class="inline-flex items-center gap-1 text-xs"
            style="color: var(--ink-dim);"
        >
            <x-dashy.icon name="chevron-left" class="size-3" />
            {{ __('All teams') }}
        </a>
        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
            <x-dashy.avatar
                size="lg"
                :name="$team->name"
                :initials="$team->initials()"
                :src="$team->logo"
            />
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="truncate font-display text-3xl" style="color: var(--ink);">
                        {{ $team->name }}
                    </h1>
                    @if ($team->personal_team)
                        <span
                            class="rounded-full px-2 py-0.5 text-xs"
                            style="background-color: var(--surface-2); color: var(--ink-muted);"
                        >
                            {{ __('Personal') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sole-owner notice (passive) --}}
    @if ($team->members->count() === 1 && $isOwner && ! $team->personal_team)
        <p class="text-sm" style="color: var(--ink-muted);">
            {{ __('You\'re the only member. Add someone to collaborate, or delete the team if you no longer need it.') }}
        </p>
    @endif

    {{-- Settings (logo + name) --}}
    @if ($isOwner)
        <x-dashy.card padding="md">
            <x-dashy.section-heading
                :title="__('Settings')"
                :description="__('Logo and name shown across Dashy.')"
            />

            {{-- Logo row --}}
            <div class="mt-5 flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:gap-6">
                <x-dashy.avatar
                    size="lg"
                    :name="$team->name"
                    :initials="$team->initials()"
                    :src="$team->logo"
                />
                <div class="flex flex-wrap gap-3">
                    <input
                        type="file"
                        wire:model="newLogo"
                        id="team-logo-input"
                        class="sr-only"
                        accept="image/jpeg,image/png,image/webp"
                    />
                    <x-dashy.button
                        type="button"
                        variant="filled"
                        x-on:click="document.getElementById('team-logo-input').click()"
                        data-test="upload-logo-button"
                    >
                        {{ __('Upload new') }}
                    </x-dashy.button>
                    @if ($team->logo)
                        <x-dashy.button
                            type="button"
                            variant="ghost"
                            wire:click="removeLogo"
                            data-test="remove-logo-button"
                        >
                            {{ __('Remove') }}
                        </x-dashy.button>
                    @endif
                </div>
            </div>
            @error('logo')
                <x-dashy.text class="mt-3" style="color: var(--state-error);">{{ $message }}</x-dashy.text>
            @enderror
            @error('newLogo')
                <x-dashy.text class="mt-3" style="color: var(--state-error);">{{ $message }}</x-dashy.text>
            @enderror

            <div class="mt-5 border-t" style="border-color: var(--border);"></div>

            {{-- Name form --}}
            <form wire:submit="rename" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-dashy.input
                        wire:model="newName"
                        :label="__('Name')"
                        required
                        autocomplete="off"
                        data-test="rename-team-name"
                    />
                </div>
                <x-dashy.button variant="primary" type="submit" class="w-full sm:w-auto" data-test="rename-team-button">
                    {{ __('Save') }}
                </x-dashy.button>
            </form>
        </x-dashy.card>
    @endif

    {{-- Billing --}}
    @if ($isOwner)
        <x-dashy.card padding="md">
            <x-dashy.section-heading
                :title="__('Billing')"
                :description="__('Default hourly rate billed for this team\'s work.')"
            />
            <form wire:submit="updateRate" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-dashy.input
                        wire:model="hourlyRate"
                        errorKey="hourly_rate"
                        :label="__('Rate')"
                        type="number"
                        step="0.01"
                        min="0"
                        inputmode="decimal"
                        autocomplete="off"
                        data-test="team-rate-input"
                    />
                </div>
                <div class="flex-1">
                    <x-dashy.select
                        wire:model="currency"
                        :label="__('Currency')"
                        data-test="team-rate-currency"
                    >
                        <option value="">{{ __('Select…') }}</option>
                        @foreach (Currency::cases() as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </x-dashy.select>
                </div>
                <x-dashy.button variant="primary" type="submit" class="w-full sm:w-auto" data-test="update-rate-button">
                    {{ __('Save') }}
                </x-dashy.button>
            </form>
        </x-dashy.card>
    @endif

    {{-- Members --}}
    <x-dashy.card padding="md">
        <x-dashy.section-heading
            :title="__('Members')"
            :description="trans_choice('{1} :count person has access to this team.|[2,*] :count people have access to this team.', $team->members->count(), ['count' => $team->members->count()])"
        />

        <div class="mt-5 flex flex-col gap-2">
            @foreach ($team->members as $member)
                @php
                    $memberRole = $member->pivot->role ?? null;
                    $memberRoleEnum = $memberRole instanceof TeamRole
                        ? $memberRole
                        : ($memberRole !== null ? TeamRole::from($memberRole) : null);
                    $isSelf = $member->is(auth()->user());
                    $isMemberOwner = $memberRoleEnum === TeamRole::Owner;
                    $teamOwnersCount = $team->members
                        ->filter(function ($m) {
                            $r = $m->pivot->role ?? null;
                            $v = $r instanceof TeamRole ? $r->value : $r;
                            return $v === TeamRole::Owner->value;
                        })
                        ->count();
                @endphp
                <div
                    wire:key="member-{{ $member->id }}"
                    class="flex flex-col gap-3 rounded-xl border p-4 sm:flex-row sm:items-center sm:justify-between"
                    style="border-color: var(--border-mid); background-color: var(--surface);"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <x-dashy.avatar
                            size="sm"
                            :name="$member->name"
                            :initials="$member->initials()"
                            :src="$member->avatar"
                        />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium" style="color: var(--ink);">
                                {{ $member->name }}
                                @if ($isSelf)
                                    <span class="text-xs font-normal" style="color: var(--ink-dim);">{{ __('(you)') }}</span>
                                @endif
                            </p>
                            <p class="truncate text-xs" style="color: var(--ink-muted);">{{ $member->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs"
                            style="background-color: {{ $isMemberOwner ? 'var(--accent)' : 'var(--surface-2)' }}; color: {{ $isMemberOwner ? 'var(--bg-deep)' : 'var(--ink-muted)' }};"
                        >
                            {{ $this->roleLabel($memberRoleEnum) }}
                        </span>

                        @if ($isOwner && ! $isSelf && (! $isMemberOwner || $teamOwnersCount > 1))
                            <x-dashy.button
                                type="button"
                                variant="ghost"
                                size="sm"
                                wire:click="confirmRemoveMember({{ $member->id }})"
                                data-test="remove-member-{{ $member->id }}"
                            >
                                {{ __('Remove') }}
                            </x-dashy.button>
                        @endif

                        @if ($isSelf && ! $team->personal_team && ! $this->isLastOwner)
                            <x-dashy.button
                                type="button"
                                variant="ghost"
                                size="sm"
                                wire:click="confirmRemoveMember({{ $member->id }})"
                                data-test="leave-team-button"
                            >
                                {{ __('Leave') }}
                            </x-dashy.button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Add member --}}
        @if ($isOwner)
            <form wire:submit="addMember" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-dashy.input
                        wire:model="inviteEmail"
                        type="email"
                        :label="__('Add member by email')"
                        :placeholder="__('person@example.com')"
                        autocomplete="off"
                        data-test="add-member-email"
                    />
                </div>
                <x-dashy.button variant="primary" type="submit" class="w-full sm:w-auto" data-test="add-member-button">
                    {{ __('Add member') }}
                </x-dashy.button>
            </form>
        @endif
    </x-dashy.card>

    {{-- Danger zone --}}
    @if ($isOwner && ! $team->personal_team)
        <x-dashy.card padding="md">
            <div class="dashy-section-heading">
                <div class="min-w-0 flex-1">
                    <h2 class="dashy-section-heading-title" style="color: var(--state-error);">{{ __('Danger zone') }}</h2>
                    <p class="dashy-section-heading-description">
                        {{ __('Deleting this team removes it for everyone and cannot be undone.') }}
                    </p>
                </div>
            </div>
            <div class="mt-5">
                <x-dashy.modal.trigger name="confirm-delete-team">
                    <x-dashy.button variant="danger" data-test="delete-team-button">
                        {{ __('Delete team') }}
                    </x-dashy.button>
                </x-dashy.modal.trigger>
            </div>
        </x-dashy.card>
    @endif

    {{-- Confirm remove member modal --}}
    <x-dashy.modal name="confirm-remove-member" focusable class="max-w-md" wire:close="cancelRemoveMember">
        <div class="space-y-4">
            <x-dashy.heading size="lg">{{ __('Remove this member?') }}</x-dashy.heading>
            <x-dashy.subheading>
                {{ __('They will lose access to the team immediately.') }}
            </x-dashy.subheading>
            <div class="flex justify-end gap-2">
                <x-dashy.modal.close>
                    <x-dashy.button type="button" variant="filled" wire:click="cancelRemoveMember">
                        {{ __('Cancel') }}
                    </x-dashy.button>
                </x-dashy.modal.close>
                <x-dashy.button variant="danger" wire:click="removeMember" data-test="confirm-remove-member">
                    {{ __('Remove') }}
                </x-dashy.button>
            </div>
        </div>
    </x-dashy.modal>

    {{-- Confirm delete team modal --}}
    @if ($isOwner && ! $team->personal_team)
        <x-dashy.modal name="confirm-delete-team" focusable class="max-w-md">
            <div class="space-y-4">
                <x-dashy.heading size="lg">{{ __('Delete this team?') }}</x-dashy.heading>
                <x-dashy.subheading>
                    {{ __('Every member will lose access. This cannot be undone.') }}
                </x-dashy.subheading>
                <div class="flex justify-end gap-2">
                    <x-dashy.modal.close>
                        <x-dashy.button type="button" variant="filled">{{ __('Cancel') }}</x-dashy.button>
                    </x-dashy.modal.close>
                    <x-dashy.button variant="danger" wire:click="deleteTeam" data-test="confirm-delete-team">
                        {{ __('Delete') }}
                    </x-dashy.button>
                </div>
            </div>
        </x-dashy.modal>
    @endif
</div>
