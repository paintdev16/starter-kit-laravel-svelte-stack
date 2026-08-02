<?php

namespace App\Actions\Avatars;

use App\Models\User;
use App\Services\AvatarService;

class ListAvatars
{
    public function __construct(private AvatarService $avatars) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(User $user): array
    {
        return $this->avatars->listFor($user);
    }
}
