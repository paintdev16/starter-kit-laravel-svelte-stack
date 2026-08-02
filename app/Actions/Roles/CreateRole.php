<?php

namespace App\Actions\Roles;

use App\Services\ActivityLoggerService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class CreateRole
{
    public function __construct(private RoleService $roles) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Request $request, array $data): Role
    {
        $role = $this->roles->create($data);

        ActivityLoggerService::log($request, 'role.created', "Rol creado: \"{$role->name}\"");

        return $role;
    }
}
