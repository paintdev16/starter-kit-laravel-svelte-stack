<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get()->map(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
            'created_at' => $role->created_at,
        ]);

        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        ActivityLoggerService::log($request, 'role.created', "Rol creado: \"{$role->name}\"");

        return response()->json($role->load('permissions'), 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($role->name === 'root' && $request->has('permissions')) {
            return response()->json(['error' => 'No puedes modificar los permisos del rol root.'], 403);
        }

        $role->update(['name' => $data['name']]);

        if ($request->has('permissions')) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        ActivityLoggerService::log($request, 'role.updated', "Rol actualizado: \"{$role->name}\"");

        return response()->json($role->load('permissions'));
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        if ($role->name === 'root') {
            return response()->json(['error' => 'No puedes eliminar el rol root.'], 403);
        }

        $roleName = $role->name;
        $role->delete();

        ActivityLoggerService::log($request, 'role.deleted', "Rol eliminado: \"{$roleName}\"");

        return response()->json(null, 204);
    }
}
