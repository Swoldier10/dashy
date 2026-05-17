<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\CreateUserAction;
use App\Domains\Auth\Actions\FindUserByEmailAction;
use App\Domains\Auth\Actions\FindUserByGoogleIdAction;
use App\Domains\Auth\Actions\UpdateUserAction;
use App\Domains\Auth\DTOs\GoogleProfile;
use App\Domains\Auth\DTOs\SocialAuthResult;
use App\Domains\Auth\Exceptions\SocialAuthException;
use App\Domains\Teams\Services\EnsurePersonalTeamService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

final class SocialAuthService
{
    public function __construct(
        private FindUserByGoogleIdAction $findByGoogleId,
        private FindUserByEmailAction $findByEmail,
        private CreateUserAction $createUser,
        private UpdateUserAction $updateUser,
        private EnsurePersonalTeamService $ensurePersonalTeam,
    ) {}

    public function redirectToGoogle(): RedirectResponse
    {
        /** @var Provider $driver */
        $driver = Socialite::driver('google');

        return $driver->redirect();
    }

    public function handleGoogleCallback(): SocialAuthResult
    {
        try {
            /** @var Provider $driver */
            $driver = Socialite::driver('google');
            $socialiteUser = $driver->user();
        } catch (Throwable $e) {
            report($e);
            throw new SocialAuthException('Google authentication failed.', 0, $e);
        }

        $profile = GoogleProfile::fromSocialite($socialiteUser);

        return DB::transaction(function () use ($profile) {
            $existingByGoogleId = $this->findByGoogleId->execute($profile->id);
            if ($existingByGoogleId !== null) {
                $this->ensurePersonalTeam->execute($existingByGoogleId);

                return new SocialAuthResult($existingByGoogleId, isNewUser: false);
            }

            $existingByEmail = $this->findByEmail->execute($profile->email);
            if ($existingByEmail !== null) {
                $linked = $this->updateUser->execute(
                    $existingByEmail,
                    $this->linkAttributes($existingByEmail, $profile),
                );
                $this->ensurePersonalTeam->execute($linked);

                return new SocialAuthResult($linked, isNewUser: false);
            }

            $created = $this->createUser->execute($this->newUserAttributes($profile));
            $this->ensurePersonalTeam->execute($created);

            return new SocialAuthResult($created, isNewUser: true);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function linkAttributes(User $user, GoogleProfile $profile): array
    {
        return [
            'google_id' => $profile->id,
            'avatar' => $user->avatar ?? $profile->avatar,
            'email_verified_at' => $user->email_verified_at
                ?? ($profile->emailVerified ? now() : null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newUserAttributes(GoogleProfile $profile): array
    {
        return [
            'salutation' => null,
            'first_name' => $profile->firstName,
            'last_name' => $profile->lastName,
            'name' => $this->composeLegacyName($profile->firstName, $profile->lastName, $profile->email),
            'email' => $profile->email,
            'password' => null,
            'google_id' => $profile->id,
            'avatar' => $profile->avatar,
            'email_verified_at' => $profile->emailVerified ? now() : null,
        ];
    }

    private function composeLegacyName(?string $firstName, ?string $lastName, string $emailFallback): string
    {
        $name = trim(implode(' ', array_filter([$firstName, $lastName])));

        return $name !== '' ? $name : $emailFallback;
    }
}
