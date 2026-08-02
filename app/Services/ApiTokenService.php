<?php

namespace App\Services;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listFor(User $user): array
    {
        return $user->tokens()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($token): array => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
            ])
            ->all();
    }

    /**
     * @param  array<int, string>  $abilities
     * @return array{accessToken: PersonalAccessToken, plainTextToken: string}
     */
    public function create(User $user, string $name, array $abilities): array
    {
        $token = $user->createToken($name, $abilities);

        return [
            'accessToken' => $token->accessToken,
            'plainTextToken' => $token->plainTextToken,
        ];
    }

    public function delete(User $user, int $tokenId): void
    {
        $user->tokens()->findOrFail($tokenId)->delete();
    }
}
