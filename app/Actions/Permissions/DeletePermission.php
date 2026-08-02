<?php

namespace App\Actions\Permissions;

use App\Services\ActivityLoggerService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class DeletePermission
{
    public function __construct(private PermissionService $permissions) {}

    public function __invoke(Request $request, Permission $permission): void
    {
        $permissionName = $permission->name;

        $this->permissions->delete($permission);

        ActivityLoggerService::log($request, 'permission.deleted', "Permiso eliminado: \"{$permissionName}\"");
    }
}
