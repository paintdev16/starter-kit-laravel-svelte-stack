<?php

namespace App\Actions\Avatars;

use App\Models\Avatar;
use App\Services\AvatarService;

class DeleteAvatar
{
    public function __construct(private AvatarService $avatars) {}

    public function __invoke(Avatar $avatar): void
    {
        $this->avatars->delete($avatar);
    }
}
