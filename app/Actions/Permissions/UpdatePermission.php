<?php

namespace App\Actions\Permissions;

use App\Services\ActivityLoggerService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UpdatePermission
{
    public function __construct(private PermissionService $permissions) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Request $request, Permission $permission, array $data): Permission
    {
        $permission = $this->permissions->update($permission, $data);

        ActivityLoggerService::log($request, 'permission.updated', "Permiso actualizado: \"{$permission->name}\"");

        return $permission;
    }
}
