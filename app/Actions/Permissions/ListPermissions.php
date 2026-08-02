<?php

namespace App\Actions\Permissions;

use App\Services\PermissionService;

class ListPermissions
{
    public function __construct(private PermissionService $permissions) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return $this->permissions->all();
    }
}
