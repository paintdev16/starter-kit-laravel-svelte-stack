<?php

namespace App\Actions\Roles;

use App\Enums\ActivityAction;
use App\Services\ActivityLoggerService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    public function __construct(private RoleService $roles) {}

    public function __invoke(Request $request, Role $role): void
    {
        $roleName = $role->name;

        $this->roles->delete($role);

        ActivityLoggerService::log($request, ActivityAction::RoleDeleted, "Rol eliminado: \"{$roleName}\"");
    }
}
