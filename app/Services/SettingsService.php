<?php

namespace App\Services;

use App\Models\User;

class SettingsService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    public function deleteProfile(User $user): void
    {
        $user->delete();
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }
}
