<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\UpdateUserAction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class AvatarService
{
    public function __construct(
        private UpdateUserAction $updateUser,
    ) {}

    public function upload(User $user, UploadedFile $file): User
    {
        Validator::make(['avatar' => $file], [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ])->validate();

        $oldUrl = $user->avatar;
        $newPath = $file->storePublicly("avatars/{$user->id}", 'public');
        $newUrl = Storage::disk('public')->url($newPath);

        try {
            $updated = DB::transaction(fn () => $this->updateUser->execute($user, ['avatar' => $newUrl]));
        } catch (Throwable $e) {
            Storage::disk('public')->delete($newPath);
            throw $e;
        }

        $this->deleteIfLocal($oldUrl);

        return $updated;
    }

    public function remove(User $user): User
    {
        $this->deleteIfLocal($user->avatar);

        return DB::transaction(fn () => $this->updateUser->execute($user, ['avatar' => null]));
    }

    private function deleteIfLocal(?string $url): void
    {
        if ($url === null) {
            return;
        }

        $publicPrefix = Storage::disk('public')->url('');

        if (! str_starts_with($url, $publicPrefix)) {
            return;
        }

        $relative = ltrim(substr($url, strlen($publicPrefix)), '/');

        Storage::disk('public')->delete($relative);
    }
}
