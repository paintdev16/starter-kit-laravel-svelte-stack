<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\UserService;

class ToggleVerification
{
    public function __construct(private UserService $users) {}

    public function __invoke(User $user): User
    {
        return $this->users->toggleVerification($user);
    }
}
