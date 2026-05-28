<?php

use App\Domains\Teams\DTOs\VisitorInvitationView;
use App\Domains\Teams\Exceptions\InvitationAlreadyAcceptedException;
use App\Domains\Teams\Exceptions\InvitationEmailMismatchException;
use App\Domains\Teams\Exceptions\InvitationExpiredException;
use App\Domains\Teams\Exceptions\InvitationRevokedException;
use App\Domains\Teams\Exceptions\TeamInvitationException;
use App\Domains\Teams\Services\AcceptTeamInvitationService;
use App\Domains\Teams\Services\ResolveInvitationForVisitorService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Invitation')]
#[Layout('layouts.auth')]
class extends Component
{
    use DispatchesDashyUi;

    public string $token = '';

    public function mount(string $token): void
    {
        $this->token = $token;

        $invitationView = $this->invitationView;
        if (in_array($invitationView->status, [
            VisitorInvitationView::STATUS_NEEDS_LOGIN,
            VisitorInvitationView::STATUS_NEEDS_REGISTER,
        ], true)) {
            session()->put('invitation.pending_token', $this->token);
        }
    }

    #[Computed]
    public function invitationView(): VisitorInvitationView
    {
        return app(ResolveInvitationForVisitorService::class)
            ->execute($this->token, Auth::user());
    }

    public function accept(AcceptTeamInvitationService $service): void
    {
        if (Auth::user() === null) {
            return;
        }

        try {
            $invitation = $service->execute(Auth::user(), $this->token);
        } catch (InvitationRevokedException) {
            unset($this->invitationView);
            $this->toast('danger', __('This invitation was revoked.'));

            return;
        } catch (InvitationExpiredException) {
            unset($this->invitationView);
            $this->toast('danger', __('This invitation expired.'));

            return;
        } catch (InvitationEmailMismatchException) {
            unset($this->invitationView);
            $this->toast('danger', __('This invitation was sent to a different email.'));

            return;
        } catch (InvitationAlreadyAcceptedException) {
            unset($this->invitationView);
            $this->toast('danger', __('This invitation was already used.'));

            return;
        } catch (TeamInvitationException) {
            unset($this->invitationView);
            $this->toast('danger', __('This invitation is no longer valid.'));

            return;
        }

        session()->forget('invitation.pending_token');
        $this->toast('success', __('You joined :team.', ['team' => $invitation->team->name]));
        $this->redirect(route('teams.show', $invitation->team), navigate: true);
    }

    public function redirectToTeam(): void
    {
        $invitationView = $this->invitationView;
        if ($invitationView->team === null) {
            return;
        }

        $this->toast('info', __('You\'re already a member of :team.', ['team' => $invitationView->team->name]));
        $this->redirect(route('teams.show', $invitationView->team), navigate: true);
    }
}; ?>

@php($view = $this->invitationView)

<div class="mx-auto w-full max-w-md py-12">
    @switch($view->status)
        @case(VisitorInvitationView::STATUS_INVALID)
            <div class="text-center space-y-4">
                <x-dashy.heading size="lg">{{ __('Invitation not found') }}</x-dashy.heading>
                <x-dashy.text>{{ __('This invitation link is invalid.') }}</x-dashy.text>
                <x-dashy.button :href="route('home')" variant="ghost">
                    {{ __('Back to Dashy') }}
                </x-dashy.button>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_EXPIRED)
            <div class="text-center space-y-4">
                <x-dashy.heading size="lg">{{ __('Invitation expired') }}</x-dashy.heading>
                <x-dashy.text>
                    {{ __('This invitation expired on :date.', ['date' => $view->expiresAt?->format('F j, Y')]) }}
                    @if ($view->inviter)
                        {{ __('Ask :name to send a new one.', ['name' => $view->inviter->name]) }}
                    @endif
                </x-dashy.text>
                <x-dashy.button :href="route('home')" variant="ghost">
                    {{ __('Back to Dashy') }}
                </x-dashy.button>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_REVOKED)
            <div class="text-center space-y-4">
                <x-dashy.heading size="lg">{{ __('Invitation revoked') }}</x-dashy.heading>
                <x-dashy.text>{{ __('This invitation was revoked.') }}</x-dashy.text>
                <x-dashy.button :href="route('home')" variant="ghost">
                    {{ __('Back to Dashy') }}
                </x-dashy.button>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_ACCEPTED_BY_OTHER)
            <div class="text-center space-y-4">
                <x-dashy.heading size="lg">{{ __('Invitation already used') }}</x-dashy.heading>
                <x-dashy.text>{{ __('This invitation has already been used.') }}</x-dashy.text>
                <x-dashy.button :href="route('home')" variant="ghost">
                    {{ __('Back to Dashy') }}
                </x-dashy.button>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_EMAIL_MISMATCH)
            <div class="text-center space-y-4">
                <x-dashy.heading size="lg">{{ __('Wrong account') }}</x-dashy.heading>
                <x-dashy.text>
                    {{ __('This invitation was sent to :bound. You\'re signed in as :current.', [
                        'bound' => $view->boundEmail,
                        'current' => Auth::user()?->email,
                    ]) }}
                </x-dashy.text>
                <x-dashy.text>
                    {{ __('Sign out and sign back in as :bound to accept.', ['bound' => $view->boundEmail]) }}
                </x-dashy.text>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dashy.button type="submit" variant="filled">{{ __('Log out') }}</x-dashy.button>
                </form>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_ALREADY_MEMBER)
            <div wire:init="redirectToTeam" class="text-center space-y-4">
                <x-dashy.heading size="lg">{{ __('You\'re already a member') }}</x-dashy.heading>
                <x-dashy.text>{{ __('Redirecting you to :team…', ['team' => $view->team?->name]) }}</x-dashy.text>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_READY_TO_ACCEPT)
            <div class="text-center space-y-5">
                <x-dashy.heading size="lg">
                    {{ __(':inviter invited you to :team', [
                        'inviter' => $view->inviter?->name ?? __('A teammate'),
                        'team' => $view->team?->name,
                    ]) }}
                </x-dashy.heading>
                <x-dashy.text>
                    {{ __('You\'ll join as :role.', ['role' => $view->role?->label()]) }}
                </x-dashy.text>
                <x-dashy.button wire:click="accept" variant="primary" class="w-full sm:w-auto" data-test="accept-invitation">
                    {{ __('Accept invitation') }}
                </x-dashy.button>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_NEEDS_LOGIN)
            <div class="text-center space-y-5">
                <x-dashy.heading size="lg">
                    {{ __('Join :team', ['team' => $view->team?->name]) }}
                </x-dashy.heading>
                <x-dashy.text>
                    {{ __(':inviter invited you to join as :role. Sign in as :bound to continue.', [
                        'inviter' => $view->inviter?->name ?? __('A teammate'),
                        'role' => $view->role?->label(),
                        'bound' => $view->boundEmail,
                    ]) }}
                </x-dashy.text>
                <x-dashy.button :href="route('login', ['email' => $view->boundEmail])" variant="primary" class="w-full sm:w-auto">
                    {{ __('Sign in') }}
                </x-dashy.button>
            </div>
            @break

        @case(VisitorInvitationView::STATUS_NEEDS_REGISTER)
            <div class="text-center space-y-5">
                <x-dashy.heading size="lg">
                    {{ __('Join :team', ['team' => $view->team?->name]) }}
                </x-dashy.heading>
                <x-dashy.text>
                    {{ __(':inviter invited you to join as :role. Create your account to accept.', [
                        'inviter' => $view->inviter?->name ?? __('A teammate'),
                        'role' => $view->role?->label(),
                    ]) }}
                </x-dashy.text>
                <x-dashy.button :href="route('register', ['email' => $view->boundEmail])" variant="primary" class="w-full sm:w-auto">
                    {{ __('Create account') }}
                </x-dashy.button>
            </div>
            @break
    @endswitch
</div>
