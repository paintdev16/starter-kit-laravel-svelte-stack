<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UpdateUser
{
    public function __construct(private UserService $users) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Request $request, User $user, array $data, ?bool $verified = null): User
    {
        $user = $this->users->update($user, $data, $verified);

        ActivityLoggerService::log($request, 'user.updated', "Usuario \"{$user->name}\" actualizado");

        return $user;
    }
}
