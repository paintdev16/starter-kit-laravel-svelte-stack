<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::all()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'guard_name' => $p->guard_name,
        ]);

        return response()->json($permissions);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);

        $root = Role::where('name', 'root')->first();
        if ($root) {
            $root->givePermissionTo($permission);
        }

        ActivityLoggerService::log($request, 'permission.created', "Permiso creado: \"{$permission->name}\"");

        return response()->json([
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ], 201);
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name,'.$permission->id,
        ]);

        $permission->update(['name' => $data['name']]);

        ActivityLoggerService::log($request, 'permission.updated', "Permiso actualizado: \"{$permission->name}\"");

        return response()->json([
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ]);
    }

    public function destroy(Request $request, Permission $permission): JsonResponse
    {
        $permissionName = $permission->name;
        $permission->delete();

        ActivityLoggerService::log($request, 'permission.deleted', "Permiso eliminado: \"{$permissionName}\"");

        return response()->json(null, 204);
    }
}
