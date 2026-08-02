<?php

namespace App\Actions\Roles;

use App\Services\RoleService;

class ListRoles
{
    public function __construct(private RoleService $roles) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return $this->roles->all();
    }
}
