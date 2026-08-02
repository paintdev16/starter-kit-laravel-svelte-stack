<?php

namespace App\Actions\Permissions;

use App\Enums\ActivityAction;
use App\Services\ActivityLoggerService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class CreatePermission
{
    public function __construct(private PermissionService $permissions) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Request $request, array $data): Permission
    {
        $permission = $this->permissions->create($data);

        ActivityLoggerService::log($request, ActivityAction::PermissionCreated, "Permiso creado: \"{$permission->name}\"");

        return $permission;
    }
}
