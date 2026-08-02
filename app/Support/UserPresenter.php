<?php

namespace App\Support;

use App\Models\User;

class UserPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->currentAvatarUrl(),
            'email_verified_at' => $user->email_verified_at,
            'has_two_factor' => $user->two_factor_confirmed_at !== null,
            'has_passkeys' => $user->passkeys()->exists(),
            'roles' => $user->getRoleNames(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function toProfileArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
        ];
    }
}
