<?php

namespace App\Actions\Tokens;

use App\Models\User;
use App\Services\ApiTokenService;
use Laravel\Sanctum\PersonalAccessToken;

class CreateApiToken
{
    public function __construct(private ApiTokenService $tokens) {}

    /**
     * @param  array<int, string>  $abilities
     * @return array{accessToken: PersonalAccessToken, plainTextToken: string}
     */
    public function __invoke(User $user, string $name, array $abilities): array
    {
        return $this->tokens->create($user, $name, $abilities);
    }
}
