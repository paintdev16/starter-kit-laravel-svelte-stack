<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Permissions\CreatePermission;
use App\Actions\Permissions\DeletePermission;
use App\Actions\Permissions\ListPermissions;
use App\Actions\Permissions\UpdatePermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private readonly ListPermissions $listPermissions,
        private readonly CreatePermission $createPermission,
        private readonly UpdatePermission $updatePermission,
        private readonly DeletePermission $deletePermission,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('view-roles'), 403);

        return response()->json(($this->listPermissions)());
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['root', 'super-admin']), 403);

        $permission = ($this->createPermission)($request, $request->validated());

        return response()->json([
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ], 201);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['root', 'super-admin']), 403);

        $permission = ($this->updatePermission)($request, $permission, $request->validated());

        return response()->json([
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
        ]);
    }

    public function destroy(Request $request, Permission $permission): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['root', 'super-admin']), 403);

        ($this->deletePermission)($request, $permission);

        return response()->json(null, 204);
    }
}
