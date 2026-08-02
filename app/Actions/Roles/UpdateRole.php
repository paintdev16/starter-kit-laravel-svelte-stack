<?php

namespace App\Actions\Roles;

use App\Enums\ActivityAction;
use App\Services\ActivityLoggerService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    public function __construct(private RoleService $roles) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Request $request, Role $role, array $data): Role
    {
        $role = $this->roles->update($role, $data);

        ActivityLoggerService::log($request, ActivityAction::RoleUpdated, "Rol actualizado: \"{$role->name}\"");

        return $role;
    }
}
