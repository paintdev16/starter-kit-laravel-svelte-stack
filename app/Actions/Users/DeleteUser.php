<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\UserService;
use Illuminate\Http\Request;

class DeleteUser
{
    public function __construct(private UserService $users) {}

    public function __invoke(Request $request, User $user): void
    {
        $userName = $user->name;

        $this->users->delete($user);

        ActivityLoggerService::log($request, 'user.deleted', "Usuario \"{$userName}\" eliminado");
    }
}
