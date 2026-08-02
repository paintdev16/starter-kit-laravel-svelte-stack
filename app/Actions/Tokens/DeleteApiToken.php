<?php

namespace App\Actions\Tokens;

use App\Models\User;
use App\Services\ApiTokenService;

class DeleteApiToken
{
    public function __construct(private ApiTokenService $tokens) {}

    public function __invoke(User $user, int $tokenId): void
    {
        $this->tokens->delete($user, $tokenId);
    }
}
