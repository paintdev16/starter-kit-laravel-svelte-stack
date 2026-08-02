<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\UserService;
use Illuminate\Http\Request;

class CreateUser
{
    public function __construct(private UserService $users) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Request $request, array $data): User
    {
        $user = $this->users->create($data);

        ActivityLoggerService::log($request, 'user.created', "Usuario \"{$user->name}\" creado");

        return $user;
    }
}
