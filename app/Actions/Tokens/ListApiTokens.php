<?php

namespace App\Actions\Tokens;

use App\Models\User;
use App\Services\ApiTokenService;

class ListApiTokens
{
    public function __construct(private ApiTokenService $tokens) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(User $user): array
    {
        return $this->tokens->listFor($user);
    }
}
