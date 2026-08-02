<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Roles\CreateRole;
use App\Actions\Roles\DeleteRole;
use App\Actions\Roles\ListRoles;
use App\Actions\Roles\UpdateRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly ListRoles $listRoles,
        private readonly CreateRole $createRole,
        private readonly UpdateRole $updateRole,
        private readonly DeleteRole $deleteRole,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(($this->listRoles)());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = ($this->createRole)($request, $request->validated());

        return response()->json($role->load('permissions'), 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();

        if ($role->name === 'root' && array_key_exists('permissions', $data)) {
            return response()->json(['error' => 'No puedes modificar los permisos del rol root.'], 403);
        }

        $role = ($this->updateRole)($request, $role, $data);

        return response()->json($role->load('permissions'));
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        if ($role->name === 'root') {
            return response()->json(['error' => 'No puedes eliminar el rol root.'], 403);
        }

        ($this->deleteRole)($request, $role);

        return response()->json(null, 204);
    }
}
